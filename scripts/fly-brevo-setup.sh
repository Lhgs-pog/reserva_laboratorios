#!/usr/bin/env bash
# IP fixo de saída Fly + instruções Brevo (não há API para liberar IP)
set -euo pipefail

APP_NAME="${FLY_APP_NAME:-labhub-uniceplac-sp}"
REGION="${FLY_REGION:-gru}"

export PATH="${HOME}/.fly/bin:${PATH}"

if ! command -v fly >/dev/null 2>&1; then
  echo "Instale: curl -L https://fly.io/install.sh | sh"
  exit 1
fi

echo "→ Alocando IP fixo de saída (egress) em ${REGION}..."
fly ips allocate-egress -a "${APP_NAME}" -r "${REGION}" -y 2>/dev/null || true

echo ""
echo "→ IPs do app ${APP_NAME}:"
fly ips list -a "${APP_NAME}" 2>/dev/null | grep -E 'egress|VERSION' || fly ips list -a "${APP_NAME}"

EGRESS_V4=$(fly ips list -a "${APP_NAME}" --json 2>/dev/null | python3 -c "
import json,sys
for ip in json.load(sys.stdin):
    if ip.get('type')=='egress' and ':' not in ip.get('address',''):
        print(ip['address']); break
" 2>/dev/null || true)

if [[ -n "${EGRESS_V4}" ]]; then
  echo ""
  echo "→ Atualizando IPs de egress no Fly..."
  EGRESS_V6=$(fly ips list -a "${APP_NAME}" --json 2>/dev/null | python3 -c "
import json,sys
for ip in json.load(sys.stdin):
    if ip.get('type')=='egress' and ':' in ip.get('address',''):
        print(ip['address']); break
" 2>/dev/null || true)
  SECS=("BREVO_EGRESS_IPV4=${EGRESS_V4}")
  [[ -n "${EGRESS_V6}" ]] && SECS+=("BREVO_EGRESS_IPV6=${EGRESS_V6}")
  fly secrets set "${SECS[@]}" -a "${APP_NAME}"
fi

MACHINE=$(fly machine list -a "${APP_NAME}" --json 2>/dev/null | python3 -c "import json,sys; m=json.load(sys.stdin); print(m[0]['id'] if m else '')" 2>/dev/null || true)
if [[ -n "${MACHINE}" ]]; then
  echo ""
  echo "→ IP de saída atual da máquina (confirme após ~5 min):"
  fly machine exec "${MACHINE}" "curl -sS -4 ifconfig.me" -a "${APP_NAME}" 2>/dev/null || echo "(reinicie a máquina se ainda mostrar IP antigo)"
fi

echo ""
echo "════════════════════════════════════════════════════════════"
echo "  LIBERAR NA BREVO (manual — não existe API/CLI)"
echo "════════════════════════════════════════════════════════════"
echo ""
echo "  1. Abra: https://app.brevo.com/security/authorised_ips"
echo "     (Conta → Settings → Security → Authorized IPs)"
echo ""
if [[ -n "${EGRESS_V4}" ]]; then
  echo "  2. Clique em «Authorize IP address» e adicione:"
  echo ""
  echo "       ${EGRESS_V4}   (IPv4 — principal)"
  EGRESS_V6=$(fly ips list -a "${APP_NAME}" --json 2>/dev/null | python3 -c "
import json,sys
for ip in json.load(sys.stdin):
    if ip.get('type')=='egress' and ':' in ip.get('address',''):
        print(ip['address']); break
" 2>/dev/null || true)
  if [[ -n "${EGRESS_V6}" ]]; then
    echo "       ${EGRESS_V6}   (IPv6 — opcional)"
  fi
else
  echo "  2. Autorize o IPv4 egress listado acima."
fi
echo ""
echo "  3. Alternativa (recomendada no Fly): desative o bloqueio"
echo "     → «Deactivate blocking» / «Desativar bloqueio»"
echo ""
echo "  4. Confira o e-mail da Brevo pedindo confirmação de IP (spam)."
echo ""
echo "  Chaves no Fly:"
echo "    BREVO_API_KEY      = xkeysib-... (API HTTP, porta 443)"
echo "    MAIL_SMTP_PASSWORD = xsmtpsib-... (SMTP porta 587)"
echo ""
echo "  Definir secrets:"
echo "    export BREVO_API_KEY=xkeysib-..."
echo "    export MAIL_SMTP_PASSWORD=xsmtpsib-..."
echo "    fly secrets set BREVO_API_KEY=\$BREVO_API_KEY MAIL_SMTP_PASSWORD=\$MAIL_SMTP_PASSWORD -a ${APP_NAME}"
echo "════════════════════════════════════════════════════════════"
