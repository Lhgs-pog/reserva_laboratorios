#!/usr/bin/env bash
# Expõe o app local (Docker :8080) com URL pública grátis.
# Mantenha este terminal aberto durante a apresentação.
set -euo pipefail
echo "Certifique-se de que Docker está rodando: docker compose up -d"
echo "Iniciando túnel → http://localhost:8080"
cloudflared tunnel --url http://localhost:8080
