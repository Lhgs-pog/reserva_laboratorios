# LabHub UNICEPLAC — Deploy

Sistema **100% PHP MVC** (`app/` + entry points na raiz).

## URLs

| Ambiente | URL |
|----------|-----|
| **Fly.io (produção — SP)** | https://labhub-uniceplac-sp.fly.dev |
| **Render (desativado)** | https://labhub-uniceplac.onrender.com → 503 |
| **Local Docker** | http://localhost:8080 |

## Fly.io (São Paulo — `gru`)

App e Supabase na mesma região (sa-east-1) → menor latência no Brasil.

**Importante:** o Fly.io pede cartão cadastrado (mesmo no free tier) antes do primeiro deploy:
https://fly.io/dashboard/contatovinicius-mends-gmail-com/billing

```bash
# 1. CLI e login (uma vez)
curl -L https://fly.io/install.sh | sh
export PATH="$HOME/.fly/bin:$PATH"
fly auth login

# 2. Deploy
chmod +x scripts/*.sh
export DB_PASSWORD='senha_supabase'
export MAIL_PASSWORD='chave_brevo'   # ou RESEND_API_KEY / BREVO_API_KEY
./scripts/fly-deploy.sh
```

**Secrets sensíveis** (não vão no git): `DB_PASSWORD`, `MAIL_PASSWORD`, opcionalmente `GOOGLE_*`.

**Volume** `labhub_uploads` persiste fotos de perfil entre deploys.

**Disponibilidade:** `min_machines_running = 1` e `auto_stop_machines = off` — a máquina fica sempre ligada (sem cold start de ~15–30 s).

Após migrar, atualize `APP_URL` se usar domínio próprio:

```bash
fly secrets set APP_URL=https://seu-dominio -a labhub-uniceplac-sp
```

## E-mail Brevo + IP fixo Fly

A Brevo **não tem API/CLI** para liberar IPs — só pelo painel web.

O Fly usa IP de saída **dinâmico** por padrão. Já alocamos IP fixo (egress) em `gru`:

| Tipo | IP |
|------|-----|
| **IPv4 (autorize este)** | `209.71.94.3` |
| IPv6 (opcional) | `2a09:8280:e615:1:0:12d:4245:0` |

### Liberar na Brevo (2 minutos)

1. Abra https://app.brevo.com/security/authorised_ips  
2. **Opção A (recomendada):** clique **Desativar bloqueio** / **Deactivate blocking**  
3. **Opção B:** em **Authorize IP address**, adicione `209.71.94.3`  
4. Confira spam — e-mail da Brevo pedindo confirmação de IP  

### Script automático (Fly CLI)

```bash
./scripts/fly-brevo-setup.sh   # mostra IPs e instruções
```

Secrets no Fly:
- `BREVO_API_KEY` = chave `xkeysib-...` (API HTTPS)
- `MAIL_SMTP_PASSWORD` = chave `xsmtpsib-...` (SMTP)

## Local

```bash
cp .env.example .env
docker compose up --build
```

## Render (legado — suspenso)

Serviço suspenso via API. Use apenas o Fly.io. Para reativar (não recomendado):

```bash
curl -X POST "https://api.render.com/v1/services/srv-d8m4ad8g4nts7382v5j0/resume" \
  -H "Authorization: Bearer $RENDER_API_KEY"
```

## Logins demo (senha: `password`)

| Perfil | E-mail |
|--------|--------|
| Coordenador | admin@uniceplac.edu.br |
| Professor | professor@uniceplac.edu.br |
| Suporte | suporte@uniceplac.edu.br |
