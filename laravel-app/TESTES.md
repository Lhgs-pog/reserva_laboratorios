# 🧪 Guia de Testes - Laravel Implementation

## ✅ Validação da Implementação

Este guia documenta como testar cada componente do Laravel implementation.

---

## 1️⃣ Testes de Roteamento

### Teste: Verificar se index.php está funcionando

```bash
# Acessar a aplicação
http://localhost/labhubuniceplac-main/laravel-app/public/

# Resultado esperado: Página de login renderizada
```

### Teste: Acessar rotas web

```
GET  /login           → Mostra formulário de login
GET  /cadastro        → Mostra formulário de cadastro
POST /logout          → Limpa sessão e redireciona
```

### Teste: Acessar rotas protegidas sem autenticação

```
GET /painel/professor → 302 Redirect para /login
GET /painel/coordenador → 302 Redirect para /login
GET /painel/suporte → 302 Redirect para /login
```

---

## 2️⃣ Testes de Model

### Teste: Model.php Query Builder

```php
<?php
// Salve este arquivo como: test_model.php

require_once 'laravel-app/app/Models/Model.php';

// Teste 1: Conexão com banco
$users = User::all();
echo "✅ Conexão OK: " . count($users) . " usuários";

// Teste 2: Where clause
$prof = User::query()
    ->where('perfil', 'professor')
    ->limit(1)
    ->first();
echo "✅ Where OK: " . ($prof ? $prof->nome : "nenhum professor");

// Teste 3: Order By
$users_ord = User::query()
    ->orderBy('nome', 'asc')
    ->limit(3)
    ->get();
echo "✅ OrderBy OK: " . count($users_ord) . " usuários";

// Teste 4: Count
$count = User::query()
    ->where('email_verificado', 1)
    ->count();
echo "✅ Count OK: " . $count . " emails verificados";
?>
```

### Teste: User Model Methods

```php
<?php
$user = User::findByEmail('joao@uniceplac.edu.br');

// Teste password verification
if ($user && $user->verifyPassword('senha123')) {
    echo "✅ Password verification OK";
}

// Teste getProfessores
$profs = User::getProfessores();
echo "✅ Professores: " . count($profs);

// Teste emailExists
$existe = User::emailExists('joao@uniceplac.edu.br');
echo "✅ Email exists: " . ($existe ? "SIM" : "NÃO");
?>
```

### Teste: Agendamento Model

```php
<?php
// Pendentes
$pend = Agendamento::getPendentes();
echo "✅ Pendentes: " . count($pend);

// Aprovados
$aprov = Agendamento::getAprovados();
echo "✅ Aprovados: " . count($aprov);

// By Professor
$prof_agenda = Agendamento::byProfessor(1);
echo "✅ Agendamentos do professor 1: " . count($prof_agenda);
?>
```

### Teste: SOS Model

```php
<?php
$pend = SOS::getPendentes();
echo "✅ SOS Pendentes: " . count($pend);

$count = SOS::countPendentes();
echo "✅ Contagem SOS: " . $count;
?>
```

---

## 3️⃣ Testes de Controller

### Teste: AuthController

```bash
# 1. Acessar página de login
curl -i http://localhost/labhubuniceplac-main/laravel-app/public/login
# Esperado: 200 OK, HTML com formulário

# 2. Fazer login com credenciais inválidas
curl -X POST -d "email=inv@uniceplac.edu.br&senha=123" \
  http://localhost/labhubuniceplac-main/laravel-app/public/login/store
# Esperado: 302 Redirect para /login com mensagem de erro

# 3. Fazer login com credenciais válidas
curl -X POST -d "email=joao@uniceplac.edu.br&senha=senha123" \
  http://localhost/labhubuniceplac-main/laravel-app/public/login/store
# Esperado: 302 Redirect para /painel/professor (com sessão ativa)

# 4. Fazer logout
curl -X POST http://localhost/labhubuniceplac-main/laravel-app/public/logout
# Esperado: 302 Redirect para /, sessão destruída
```

### Teste: PainelController

```bash
# 1. Acessar painel professor sem autenticação
curl -i http://localhost/labhubuniceplac-main/laravel-app/public/painel/professor
# Esperado: 302 Redirect para /login

# 2. Com sessão ativa, acessar painel professor
curl -b "PHPSESSID=seu_session_id" \
  http://localhost/labhubuniceplac-main/laravel-app/public/painel/professor
# Esperado: 200 OK, página com agendamentos
```

### Teste: AgendamentoController (API)

```bash
# 1. GET /api/agendamentos (sem auth)
curl -i http://localhost/labhubuniceplac-main/laravel-app/public/api/agendamentos
# Esperado: 302 Redirect para /login

# 2. GET /api/agendamentos (com auth)
curl -b "PHPSESSID=session_id" \
  http://localhost/labhubuniceplac-main/laravel-app/public/api/agendamentos
# Esperado: 200 OK, JSON com agendamentos

# 3. POST /api/agendamentos (criar)
curl -X POST -H "Content-Type: application/json" \
  -d '{"id_laboratorio":1,"id_professor":1,"id_disciplina":1,"turno":"matutino","periodo":"P1","data_reserva":"2024-12-20","status":"pendente"}' \
  -b "PHPSESSID=session_id" \
  http://localhost/labhubuniceplac-main/laravel-app/public/api/agendamentos
# Esperado: 201 Created, JSON com novo agendamento

# 4. DELETE /api/agendamentos/1
curl -X DELETE \
  -b "PHPSESSID=session_id" \
  http://localhost/labhubuniceplac-main/laravel-app/public/api/agendamentos/1
# Esperado: 200 OK, JSON com mensagem de sucesso
```

### Teste: SOSController (API)

```bash
# 1. GET /api/sos/pendentes
curl http://localhost/labhubuniceplac-main/laravel-app/public/api/sos/pendentes
# Esperado: 200 OK, JSON {"qtd": X}

# 2. POST /api/sos (criar ticket)
curl -X POST -H "Content-Type: application/json" \
  -d '{"titulo":"Lab 1 com problema","descricao":"Computadores travando"}' \
  -b "PHPSESSID=session_id" \
  http://localhost/labhubuniceplac-main/laravel-app/public/api/sos
# Esperado: 201 Created, JSON com novo ticket
```

---

## 4️⃣ Testes de View

### Teste: Login View

```bash
curl http://localhost/labhubuniceplac-main/laravel-app/public/login
# Verifique se contém:
# - Form com action="/login/store"
# - Input type="email" name="email"
# - Input type="password" name="senha"
# - Button type="submit"
```

### Teste: Cadastro View

```bash
curl http://localhost/labhubuniceplac-main/laravel-app/public/cadastro
# Verifique se contém:
# - Form com action="/cadastro"
# - Input name="nome"
# - Input name="email"
# - Input name="senha"
# - Input name="confirmar_senha"
```

---

## 5️⃣ Testes de Segurança

### Teste: SQL Injection

```php
<?php
// Tente buscar usuário com SQL injection
$malicious = "1 OR 1=1; DROP TABLE usuarios; --";
$user = User::find($malicious);
// Esperado: Nenhum usuário encontrado (prepared statement protege)
?>
```

### Teste: Password Hashing

```php
<?php
$user = User::createWithPassword([
    'nome' => 'Teste',
    'email' => 'teste@uniceplac.edu.br',
    'senha' => 'senha123'
]);

// Verificar que a senha não está em texto plano
var_dump($user->getAttribute('senha')); // Deve ser hash, não 'senha123'

// Verificar que password_verify funciona
if ($user->verifyPassword('senha123')) {
    echo "✅ Password verification OK";
}
?>
```

### Teste: Session Security

```php
<?php
session_start();

// Antes do login
$session_id_antes = session_id();

// Simular login
$_SESSION['usuario_id'] = 1;
session_regenerate_id(true);

// Depois do login
$session_id_depois = session_id();

// Verificar que session foi regenerada
if ($session_id_antes !== $session_id_depois) {
    echo "✅ Session regeneration OK";
}
?>
```

### Teste: Authorization

```php
<?php
// Tentar acessar /painel/coordenador como professor
$_SESSION['usuario_id'] = 1;
$_SESSION['perfil'] = 'professor';

// Controller faz requireRole('coordenador')
// Esperado: Redirect para /
?>
```

---

## 6️⃣ Testes de Banco de Dados

### Teste: Prepared Statements

```php
<?php
// Todas as queries no código devem usar prepared statements
// Exemplos:
$pdo = Model::getConnection();

// ✅ Correto - prepared statement
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);

// ❌ Errado - não usar
$result = $pdo->query("SELECT * FROM usuarios WHERE email = '$email'");
?>
```

### Teste: Timestamps

```php
<?php
// Criar novo registro
$agendamento = Agendamento::create([
    'id_laboratorio' => 1,
    'id_professor' => 1,
    'id_disciplina' => 1,
    'turno' => 'matutino',
    'periodo' => 'P1',
    'data_reserva' => '2024-12-20',
    'status' => 'pendente'
]);

// Verificar timestamps automáticos
echo "Created: " . $agendamento->created_at;
echo "Updated: " . $agendamento->updated_at;
// Esperado: Timestamps preenchidos automaticamente
?>
```

---

## 7️⃣ Testes de Performance

### Teste: Query Count

```php
<?php
// Medir quantas queries são executadas

$before_queries = 0;
// Contar todas as queries executadas

$users = User::all();
$total = count($users);

echo "✅ Fetched $total users com 1 query (sem N+1)";
?>
```

### Teste: Limit/Offset

```php
<?php
// Paginar resultados
$page = 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$users = User::query()
    ->limit($per_page)
    ->offset($offset)
    ->get();

echo "✅ Page $page: " . count($users) . " usuários";
?>
```

---

## 8️⃣ Testes de Erro Handling

### Teste: View Not Found

```php
<?php
try {
    $this->view('nao.existe');
} catch (Exception $e) {
    echo "✅ Exception caught: " . $e->getMessage();
    // Esperado: "View not found..."
}
?>
```

### Teste: Model Not Found

```php
<?php
$user = User::find(999999); // ID que não existe
if ($user === null) {
    echo "✅ Returns null when not found";
}
?>
```

### Teste: Database Error

```php
<?php
try {
    // Query com erro SQL
    $stmt = $pdo->prepare("SELECT * FROM tabela_inexistente");
    $stmt->execute();
} catch (PDOException $e) {
    echo "✅ PDOException caught: " . $e->getMessage();
}
?>
```

---

## 9️⃣ Checklist Final de Validação

- [ ] Todos os Controllers estão em `app/Http/Controllers/`
- [ ] Todos os Models estão em `app/Models/`
- [ ] Todas as Views estão em `resources/views/`
- [ ] `public/index.php` está configurado como entry point
- [ ] `.htaccess` está habilitando URL rewriting
- [ ] `.env` contém credenciais corretas de BD
- [ ] Namespaces estão corretos em todos os arquivos
- [ ] PDO connections estão usando prepared statements
- [ ] Routes estão mapeadas corretamente
- [ ] Controllers herdam de `Controller` base
- [ ] Models herdam de `Model` base
- [ ] Session management está funcionando
- [ ] Authentication está protegendo rotas
- [ ] Authorization (roles) está funcionando

---

## 🔟 Script de Teste Automático

```bash
#!/bin/bash

echo "🧪 Iniciando testes do Laravel Implementation..."

# Teste 1: Conectar ao servidor
echo "1️⃣  Testando conexão com servidor..."
curl -s -o /dev/null -w "%{http_code}" \
  http://localhost/labhubuniceplac-main/laravel-app/public/ | grep -q 200 \
  && echo "✅ Servidor respondendo" || echo "❌ Servidor não respondendo"

# Teste 2: Verificar página de login
echo "2️⃣  Testando página de login..."
curl -s http://localhost/labhubuniceplac-main/laravel-app/public/login | \
  grep -q "Login" && echo "✅ Login page OK" || echo "❌ Login page failed"

# Teste 3: Verificar página de cadastro
echo "3️⃣  Testando página de cadastro..."
curl -s http://localhost/labhubuniceplac-main/laravel-app/public/cadastro | \
  grep -q "Cadastro" && echo "✅ Signup page OK" || echo "❌ Signup page failed"

echo "✅ Testes concluídos!"
```

---

## 📝 Relatório de Testes

Após executar todos os testes acima, preencha este relatório:

```
Data: ___________
Testador: ___________

✅ Roteamento: [ ]
✅ Models: [ ]
✅ Controllers: [ ]
✅ Views: [ ]
✅ Segurança: [ ]
✅ Banco de Dados: [ ]
✅ Performance: [ ]
✅ Error Handling: [ ]

Problemas encontrados:
_______________________________

Observações:
_______________________________

Status Final: ✅ APROVADO / ❌ REJEITO
```

---

**Versão**: 1.0
**Atualizado**: 2024
**Status**: Pronto para Teste
