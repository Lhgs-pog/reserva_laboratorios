# LabHub UNICEPLAC

Sistema de reserva de laboratorios — PHP 8.2, MVC leve, PostgreSQL (Supabase) em producao.

**Producao:** https://labhub-uniceplac-sp.fly.dev

## Estrutura do projeto

```
.
├── app/                    # Aplicacao MVC
│   ├── Config/             # Env, DB, helpers (SOS, feriados, calendario)
│   ├── Controllers/        # Auth, Coordenador, Agendamento, SOS, etc.
│   ├── Models/             # Agendamento, User, SOS
│   ├── Services/           # Mail, Usuario, Feriados, SOS
│   ├── Views/              # Templates (coordenador, partials)
│   └── router.php          # Rotas legado → controllers
├── bootstrap.php           # Autoload + dispatch MVC
├── css/  js/  img/         # Assets estaticos
├── database/
│   ├── schema.sql          # Schema MySQL de referencia (dev local)
│   └── seeds/              # Dados de demonstracao
├── docs/
│   └── DEPLOY.md           # Fly.io, Brevo, Supabase
├── PHPMailer/src/          # SMTP fallback (MailService)
├── scripts/                # Deploy e utilitarios
├── supabase/migrations/    # Migrations PostgreSQL (producao)
├── uploads/                # Fotos de perfil (volume Fly)
├── index.php               # Login
├── painel_coordenador.php  # Painel coordenacao (MVC)
├── painel_professor.php    # Painel docente
├── painel_suporte.php      # Painel TI
└── conexao.php             # PDO legado (professor/suporte)
```

## Desenvolvimento local

```bash
cp .env.example .env
docker compose up -d
# App: http://localhost:8080
```

Producao usa **PostgreSQL/Supabase**; o `docker-compose` sobe **MySQL** apenas para dev offline com `database/schema.sql`.

## Deploy

Ver [docs/DEPLOY.md](docs/DEPLOY.md).

```bash
export DB_PASSWORD='...'
./scripts/fly-deploy.sh
```

## Stack

- PHP 8.2 + Apache (Docker / Fly.io)
- Bootstrap 5 + FullCalendar
- Supabase PostgreSQL
- E-mail: Resend / Brevo API ou SMTP
