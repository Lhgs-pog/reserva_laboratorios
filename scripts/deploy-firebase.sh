#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PROJECT_ID="${FIREBASE_PROJECT_ID:-labhub-uniceplac-vini}"
REGION="${CLOUD_RUN_REGION:-southamerica-east1}"
SERVICE_NAME="${CLOUD_RUN_SERVICE:-labhub-uniceplac}"
SQL_INSTANCE="${CLOUD_SQL_INSTANCE:-labhub-db}"
IMAGE="${REGION}-docker.pkg.dev/${PROJECT_ID}/labhub/${SERVICE_NAME}"

command -v gcloud >/dev/null || { echo "Instale gcloud: brew install --cask google-cloud-sdk"; exit 1; }
command -v docker >/dev/null || { echo "Docker é necessário para build da imagem."; exit 1; }

echo "==> Projeto: ${PROJECT_ID} | região: ${REGION}"
npx -y firebase-tools@latest use "${PROJECT_ID}"
gcloud config set project "${PROJECT_ID}"

echo "==> Habilitando APIs (requer billing Blaze)..."
gcloud services enable run.googleapis.com artifactregistry.googleapis.com cloudbuild.googleapis.com sqladmin.googleapis.com

echo "==> Artifact Registry..."
gcloud artifacts repositories describe labhub --location="${REGION}" >/dev/null 2>&1 \
  || gcloud artifacts repositories create labhub --repository-format=docker --location="${REGION}"

echo "==> Build e push..."
gcloud auth configure-docker "${REGION}-docker.pkg.dev" --quiet
docker build -t "${IMAGE}" .
docker push "${IMAGE}"

CONN="${PROJECT_ID}:${REGION}:${SQL_INSTANCE}"
echo "==> Deploy Cloud Run..."
gcloud run deploy "${SERVICE_NAME}" \
  --image "${IMAGE}" \
  --platform managed \
  --region "${REGION}" \
  --allow-unauthenticated \
  --port 80 \
  --add-cloudsql-instances "${CONN}" \
  --set-env-vars "CLOUD_SQL_CONNECTION_NAME=${CONN},DB_DATABASE=${DB_DATABASE:-sistema_labs},DB_USERNAME=${DB_USERNAME:-root},DB_PASSWORD=${DB_PASSWORD}"

echo "==> Hosting com rewrite ao Cloud Run..."
node -e "
const fs=require('fs');
const p='firebase.json';
const j=JSON.parse(fs.readFileSync(p,'utf8'));
j.hosting.rewrites=[{source:'**',run:{serviceId:'${SERVICE_NAME}',region:'${REGION}'}}];
fs.writeFileSync(p, JSON.stringify(j,null,2)+'\n');
"

npx -y firebase-tools@latest deploy --only hosting

echo "==> URL pública:"
npx -y firebase-tools@latest hosting:sites:list
