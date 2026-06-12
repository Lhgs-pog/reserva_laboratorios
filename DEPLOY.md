# LabHub UNICEPLAC — Deploy

> **IMPORTANTE:** Este app usa o projeto Vercel `labhub-uniceplac`.
> **Nunca** deployar no projeto `web` — ele serve a loja **venuse.com.br**.

## URLs

| Serviço | URL |
|---------|-----|
| **App LabHub (Vercel)** | https://labhub-uniceplac.vercel.app |
| **Supabase** | https://supabase.com/dashboard/project/eqawtpbvcqlcemysjkox |
| **GitHub** | https://github.com/evinicim/reserva_laboratorios |

## Perfis de demonstração

| Perfil | E-mail | Rota |
|--------|--------|------|
| Professor | professor@uniceplac.edu.br | `/professor` |
| Coordenador | admin@uniceplac.edu.br | `/coordenador` |
| Suporte | suporte@uniceplac.edu.br | `/suporte` |

## Stack

- **Frontend**: Next.js 16 → Vercel projeto `labhub-uniceplac` (região GRU1)
- **Banco**: Supabase PostgreSQL (São Paulo)
- **CI/CD**: GitHub Actions → deploy apenas em `labhub-uniceplac`

## Deploy automático

Todo `git push` na branch `main` dispara build + deploy no projeto **labhub-uniceplac** (nunca no `web`).

## Desenvolvimento local

```bash
cd web
cp .env.example .env.local
npm install
npm run dev
```

## Deploy manual (seguro)

```bash
cd web
vercel link --project labhub-uniceplac --scope evinicims-projects
vercel deploy --prod --scope evinicims-projects
```
