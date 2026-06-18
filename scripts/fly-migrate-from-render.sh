#!/usr/bin/env bash
# Copia secrets do Render para o Fly.io (requer RENDER_API_KEY no ~/.render/cli.yaml)
set -euo pipefail

APP_NAME="${FLY_APP_NAME:-labhub-uniceplac-sp}"
RENDER_SERVICE="${RENDER_SERVICE_ID:-srv-d8m4ad8g4nts7382v5j0}"

RENDER_API_KEY=$(awk '/^    key: rnd_/{print $2}' "${HOME}/.render/cli.yaml" 2>/dev/null || true)
if [[ -z "${RENDER_API_KEY}" ]]; then
  echo "Render API key não encontrada. Defina DB_PASSWORD e MAIL_PASSWORD manualmente."
  exit 1
fi

fetch_env() {
  local key="$1"
  curl -sf "https://api.render.com/v1/services/${RENDER_SERVICE}/env-vars" \
    -H "Authorization: Bearer ${RENDER_API_KEY}" \
    | python3 -c "
import json, sys
key = sys.argv[1]
for item in json.load(sys.stdin):
    ev = item.get('envVar', item)
    if ev.get('key') == key:
        print(ev.get('value', ''))
        break
" "$key"
}

DB_PASSWORD=$(fetch_env "DB_PASSWORD")
MAIL_PASSWORD=$(fetch_env "MAIL_PASSWORD")

if [[ -z "${DB_PASSWORD}" ]]; then
  echo "Erro: DB_PASSWORD não encontrado no Render."
  exit 1
fi

export DB_PASSWORD
if [[ "${MAIL_PASSWORD}" == xkeysib-* ]]; then
  export BREVO_API_KEY="${MAIL_PASSWORD}"
  echo "→ Secrets: DB_PASSWORD + BREVO_API_KEY (xkeysib do Render)"
else
  export MAIL_PASSWORD
  echo "→ Secrets: DB_PASSWORD + MAIL_PASSWORD"
fi
exec "$(dirname "$0")/fly-deploy.sh"
