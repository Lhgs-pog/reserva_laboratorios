#!/usr/bin/env bash
# Gera ZIP enxuto para upload na plataforma da faculdade (limite ~150 MB).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="${1:-$HOME/Downloads/reserva_laboratorios-labhub-entrega.zip}"

cd "$ROOT"
git archive -o "$OUT" HEAD

SIZE=$(du -h "$OUT" | cut -f1)
echo "OK: $OUT ($SIZE)"
echo "Inclui ENTREGA.txt (via git). Excluídos: .git, vendor/, node_modules/, uploads/, .env"
