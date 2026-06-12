# LabHub UNICEPLAC — Deploy

## URLs

| Serviço | URL |
|---------|-----|
| **App (Vercel)** | https://web-pfcag2aao-evinicims-projects.vercel.app |
| **Supabase** | https://supabase.com/dashboard/project/eqawtpbvcqlcemysjkox |
| **GitHub** | https://github.com/evinicim/reserva_laboratorios |

## Perfis de demonstração

| Perfil | E-mail | Senha (PHP legado) |
|--------|--------|-------------------|
| Professor | professor@uniceplac.edu.br | password |
| Coordenador | admin@uniceplac.edu.br | password |
| Suporte | suporte@uniceplac.edu.br | password |

## Stack

- **Frontend**: Next.js 16 → Vercel (região GRU1)
- **Banco**: Supabase PostgreSQL (São Paulo)
- **CI/CD**: GitHub Actions (build + deploy Vercel + migrations Supabase)
- **Backend PHP legado**: disponível via Docker/Render (`render.yaml`)

## Deploy automático

Todo `git push` na branch `main` dispara:
1. Build do Next.js
2. Deploy na Vercel
3. Migrations Supabase (`supabase db push`)

## Desenvolvimento local

```bash
cd web
cp .env.example .env.local   # preencher chaves Supabase
npm install
npm run dev
```

## Migrations Supabase

```bash
supabase link --project-ref eqawtpbvcqlcemysjkox
supabase db push
```
