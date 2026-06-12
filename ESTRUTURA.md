# 📁 ESTRUTURA DO PROJETO - ARQUITETURA MVC

```
labhubuniceplac-main/
│
├── 📂 app/                          ← NOVA ARQUITETURA MVC
│   ├── 📂 config/
│   │   └── Database.php             ← Singleton da conexão PDO
│   │
│   ├── 📂 Models/                   ← Lógica de dados
│   │   ├── BaseModel.php            ← Classe base com CRUD comum
│   │   ├── User.php                 ← Usuários (login, cadastro, google)
│   │   ├── Agendamento.php          ← Agendamentos/Reservas
│   │   └── SOS.php                  ← Chamados de suporte
│   │
│   ├── 📂 Controllers/              ← Lógica de negócio
│   │   ├── BaseController.php       ← Classe base (redirect, render, json)
│   │   ├── AuthController.php       ← Autenticação e autorização
│   │   ├── AgendamentoController.php ← Gerenciamento de agendamentos
│   │   ├── SOSController.php        ← Gerenciamento de SOS
│   │   └── PainelController.php     ← Painéis de usuários
│   │
│   ├── 📂 Views/                    ← Templates HTML (futuro)
│   │   ├── 📂 layouts/
│   │   ├── 📂 auth/
│   │   ├── 📂 painel/
│   │   ├── 📂 agendamento/
│   │   └── 📂 components/
│   │
│   └── router.php                   ← Router central com autoload
│
├── 📂 vendor/                       ← Dependências Composer (Google API, PHPMailer, etc)
│   ├── google/
│   ├── guzzlehttp/
│   ├── phpmailer/
│   └── ...
│
├── 📂 PHPMailer/                    ← Biblioteca de email (legado)
│
├── 📂 cacert/                       ← Certificados SSL
│
│
├── ⚙️ ARQUIVOS ANTIGOS (agora refatorados)
├── index.php                        ← Login (→ AuthController)
├── cadastro.php                     ← Cadastro (→ AuthController)
├── login_google.php                 ← OAuth Google (→ AuthController)
├── logout.php                       ← Logout (→ AuthController)
├── verificar.php                    ← Verificação email (→ AuthController)
├── check_sos.php                    ← Contagem SOS (→ SOSController)
├── check_sos_status.php             ← Status SOS (→ SOSController)
├── editor_agendamento.php           ← Editar agendamento (→ AgendamentoController)
├── painel_professor.php             ← Painel professor (→ PainelController)
├── painel_coordenador.php           ← Painel coordenador (→ PainelController)
├── painel_suporte.php               ← Painel suporte (→ PainelController)
├── Agendamento.php                  ← Classe legada (pode remover depois)
├── conexao.php                      ← Conexão legada (pode remover depois)
│
├── 📋 DOCUMENTAÇÃO
├── MVC_REFACTOR_README.md           ← Guia completo da refatoração
├── MVC_CHECKLIST.md                 ← Checklist de tudo que foi feito
├── ESTRUTURA.md                     ← Este arquivo
│
├── 🔧 CONFIGURAÇÃO
├── composer.json                    ← Dependências do projeto
├── composer.lock                    ← Versions lock
├── .gitignore                       ← Git ignore
│
└── 🎨 ASSETS
    ├── uniceplac.png                ← Logo
    ├── uniceplac2.png               ← Logo alternativa
    ├── google-icon.svg.png          ← Ícone Google
    └── ...outros arquivos
```

---

## 🔄 FLUXO DE REQUISIÇÃO

```
┌─────────────────────────────────────────────────────────────┐
│  CLIENTE (Browser/Aplicação)                                 │
└────────────────────┬────────────────────────────────────────┘
                     │
                     │ Request
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  index.php / cadastro.php / ... (Arquivo antigo)            │
│  └─ require 'app/router.php'                                │
└────────────────────┬────────────────────────────────────────┘
                     │
                     │ Executa Router
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  app/router.php                                              │
│  └─ Mapeia URL para Controller & Action                     │
└────────────────────┬────────────────────────────────────────┘
                     │
                     │ Instancia e executa
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  app/Controllers/SeuController.php                           │
│  ├─ Valida autenticação                                     │
│  ├─ Processa REQUEST (POST/GET)                            │
│  ├─ Chama Model conforme necessário                        │
│  └─ Retorna dados ou redireciona                           │
└────────────────────┬────────────────────────────────────────┘
                     │
              ┌──────┴──────┐
              │             │
              ▼             ▼
    ┌────────────────┐  ┌────────────────┐
    │ Model::método()│  │ redirect()     │
    │ └─ Query BD    │  │ └─ header()    │
    └────────────────┘  └────────────────┘
              │             │
              └──────┬──────┘
                     │
                     │ Retorna dados
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  Arquivo original (ex: index.php)                            │
│  └─ extract($controller_data)                              │
│  └─ Renderiza HTML com dados                               │
└────────────────────┬────────────────────────────────────────┘
                     │
                     │ Response HTML
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  CLIENTE (Browser renderiza)                                │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 EXEMPLOS DE USO

### Exemplo 1: Login de Usuário
```
POST /index.php
  ├─ router.php detecta 'index' → AuthController::login()
  ├─ AuthController valida credentials
  ├─ User::findByEmail($email)
  ├─ password_verify() compara senhas
  └─ redirect() para painel_professor.php
```

### Exemplo 2: Criar Agendamento
```
POST /Agendamento.php
  ├─ router.php detecta 'Agendamento' → AgendamentoController::criar()
  ├─ AgendamentoController valida permissão (requirePerfil)
  ├─ Agendamento::criarReserva()
  │  └─ INSERT INTO agendamentos
  └─ redirect() com mensagem de sucesso
```

### Exemplo 3: API de Contagem SOS
```
GET /check_sos.php
  ├─ router.php detecta 'check_sos' → SOSController::contarPendentes()
  ├─ SOS::contarPendentes()
  │  └─ SELECT COUNT(*) FROM chamados_suporte
  └─ json(['qtd' => 5])
```

---

## 🔐 SEGURANÇA

### Implementado ✅
- [x] Singleton Database (conexão única)
- [x] Prepared Statements em todas as queries
- [x] Password hashing com `password_hash()`
- [x] Session regeneration no login
- [x] Validação de perfil (requirePerfil)
- [x] Validação de autenticação (requireAuth)
- [x] Sanitização de entrada com htmlspecialchars()

### Recomendado 🔄
- [ ] Adicionar CSRF tokens
- [ ] Rate limiting
- [ ] Logging de ações
- [ ] 2FA para coordenadores
- [ ] Encriptação de dados sensíveis

---

## 📦 DEPENDÊNCIAS

### Instaladas via Composer
- `google/apiclient` - Autenticação Google OAuth
- `guzzlehttp/guzzle` - HTTP Client
- `phpmailer/phpmailer` - Envio de emails

### Arquivos Locais
- `app/config/Database.php` - Gerenciador de conexão
- `vendor/` - Autoload automático

---

## ⚡ PERFORMANCE

- **Autoload PSR-4**: Carrega apenas o que é necessário
- **Singleton Database**: Uma conexão PDO para toda app
- **Prepared Statements**: Compiladas uma vez, executadas múltiplas vezes
- **Sem include duplicado**: Router centraliza tudo

---

## 🚀 FÁCIL EXPANSÃO

### Adicionar Novo Controller
```php
// 1. Criar arquivo app/Controllers/NovoController.php
class NovoController extends BaseController { }

// 2. Adicionar rota em app/router.php
$routes['minha_pagina'] = ['NovoController', 'meuMetodo'];

// 3. Criar arquivo minha_pagina.php que chama o router
$controller_data = require 'app/router.php';
```

### Adicionar Novo Model
```php
// 1. Criar arquivo app/Models/Novo.php
class Novo extends BaseModel { 
    protected $table = 'minha_tabela';
}

// 2. Usar no Controller
$novo = new Novo();
$dados = $novo->findAll();
```

---

**Estrutura criada**: 7 de maio de 2026
**Status**: ✅ Completa e funcional
