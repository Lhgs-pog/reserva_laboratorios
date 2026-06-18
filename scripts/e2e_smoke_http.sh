#!/usr/bin/env bash
# Smoke E2E HTTP — login, painéis, CSRF, SOS, notificações
# Uso: ./scripts/e2e_smoke_http.sh [base_url]
set -euo pipefail

BASE="${1:-http://localhost:8080}"
COOKIE="$(mktemp)"
trap 'rm -f "$COOKIE"' EXIT

fail() { echo "FAIL: $1"; exit 1; }
ok()   { echo "OK: $1"; }

csrf_from_html() {
  grep -o 'name="csrf-token" content="[^"]*"' | head -1 | sed 's/.*content="//;s/"$//'
}

login_as() {
  local email="$1"
  local label="$2"
  rm -f "$COOKIE"
  curl -s -c "$COOKIE" -b "$COOKIE" "$BASE/index.php" -o /tmp/labhub_index.html
  local code
  code=$(curl -s -o /dev/null -w '%{http_code}' -c "$COOKIE" -b "$COOKIE" \
    -X POST "$BASE/index.php" \
    -d "email=${email}&senha=password&lembrar_me=1")
  [[ "$code" == "302" ]] || fail "login $label HTTP $code (esperado 302)"
  ok "login $label"
}

assert_http() {
  local url="$1" expect="$2" label="$3"
  local code
  code=$(curl -s -o /tmp/labhub_body.html -w '%{http_code}' -b "$COOKIE" -c "$COOKIE" "$url")
  [[ "$code" == "$expect" ]] || fail "$label HTTP $code (esperado $expect)"
  ok "$label ($code)"
}

assert_json_field() {
  local url="$1" field="$2" label="$3"
  local body code
  body=$(curl -s -b "$COOKIE" -w '\n%{http_code}' "$url")
  code=$(echo "$body" | tail -1)
  body=$(echo "$body" | sed '$d')
  [[ "$code" == "200" ]] || fail "$label HTTP $code"
  echo "$body" | grep -q "\"$field\"" || fail "$label sem campo $field"
  ok "$label"
}

# --- Index público ---
code=$(curl -s -o /dev/null -w '%{http_code}' "$BASE/index.php")
[[ "$code" == "200" ]] || fail "index público HTTP $code"
grep -q 'csrf-token' "$(curl -s "$BASE/index.php" | tee /tmp/labhub_pub.html)" 2>/dev/null || \
  curl -s "$BASE/index.php" | grep -q 'csrf-token' || fail "index sem csrf-token"
ok "index público + CSRF meta"

# --- Coordenador ---
login_as 'admin@uniceplac.edu.br' 'coordenador'
assert_http "$BASE/painel_coordenador.php" '200' 'painel coordenador'
assert_http "$BASE/coordenador" '200' 'rota amigável /coordenador'
assert_json_field "$BASE/check_notificacoes.php" 'items' 'notificações coordenador'

TOKEN=$(curl -s -b "$COOKIE" "$BASE/painel_coordenador.php" | csrf_from_html)
[[ -n "$TOKEN" ]] || fail "CSRF token ausente no painel coordenador"
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIE" \
  -X POST "$BASE/painel_coordenador.php" \
  -H "X-CSRF-Token: $TOKEN" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "ajax=1&id_agendamento=999999&acao_reserva=aprovar")
[[ "$code" == "200" ]] || fail "POST AJAX coordenador com CSRF HTTP $code"
ok "POST AJAX coordenador com CSRF ($code)"

code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIE" \
  -X POST "$BASE/painel_coordenador.php" \
  -d "ajax=1&id_agendamento=999999&acao_reserva=aprovar")
[[ "$code" == "403" ]] || fail "POST AJAX sem CSRF deveria ser 403, foi $code"
ok "POST AJAX coordenador sem CSRF bloqueado (403)"

# --- Professor: SOS negado ---
login_as 'professor@uniceplac.edu.br' 'professor'
assert_http "$BASE/painel_professor.php" '200' 'painel professor'
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIE" "$BASE/check_sos_status.php")
[[ "$code" == "403" ]] || fail "professor em check_sos_status deveria 403, foi $code"
ok "SOS bloqueado para professor (403)"

# --- Suporte: SOS permitido ---
login_as 'suporte@uniceplac.edu.br' 'suporte'
assert_http "$BASE/painel_suporte.php" '200' 'painel suporte'
body=$(curl -s -b "$COOKIE" "$BASE/check_sos_status.php")
echo "$body" | grep -q 'qtd_suporte' || fail "check_sos_status suporte sem qtd_suporte"
ok "SOS acessível para suporte"

echo ""
echo "Todos os testes E2E HTTP passaram."
