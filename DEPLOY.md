# LabHub UNICEPLAC — Deploy

Sistema **100% PHP MVC** (referência: `temp/pasta reserva-edits`).

## URLs

| Ambiente | URL |
|----------|-----|
| **Render (produção)** | https://labhub-uniceplac.onrender.com |
| **Local Docker** | http://localhost:8080 |

## Local

```bash
cp .env.example .env
docker compose up --build
```

## Render

1. https://dashboard.render.com → serviço `labhub-uniceplac`
2. Conectado ao repo GitHub — deploy automático a cada push na `main`
3. Variável `DB_PASSWORD` = senha do Supabase
4. **DB_HOST** deve ser `aws-1-sa-east-1.pooler.supabase.com` (não `aws-0`) — copie do dashboard Supabase se mudar

## Logins demo (senha: `password`)

| Perfil | E-mail |
|--------|--------|
| Coordenador | admin@uniceplac.edu.br |
| Professor | professor@uniceplac.edu.br |
| Suporte | suporte@uniceplac.edu.br |
