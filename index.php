<?php
// ============================================================
// ARQUITETURA MVC - Arquivo refatorado para usar Controllers
// ============================================================

// Inclui o router que mapeia para o Controller apropriado
$controller_data = require __DIR__ . '/app/router.php';

// Extrai dados retornados pelo controller
if (is_array($controller_data)) {
    extract($controller_data);
} else {
    $erro = '';
    $sucesso = '';
    $redirect = '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNICEPLAC - Central de Reservas</title>
    <?php require __DIR__ . '/app/Views/partials/favicon.php'; ?>
    <?php require __DIR__ . '/app/Views/partials/csrf-meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --azul-uniceplac: #00734F;
            --amarelo-uniceplac: #f07f3c;
        }

        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
            box-sizing: border-box;
        }

        .login-shell {
            width: min(100%, 28rem);
        }

        .text-uniceplac { color: var(--azul-uniceplac) !important; }

        .btn-uniceplac {
            background-color: var(--azul-uniceplac);
            color: white;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }

        .btn-uniceplac:hover {
            background-color: #045238;
            color: var(--amarelo-uniceplac);
        }

        .card-uniceplac {
            border: none;
            border-top: 6px solid var(--amarelo-uniceplac);
            border-radius: 12px;
        }
    </style>
</head>

<body>
    <main class="login-shell">
        <div class="card card-uniceplac shadow-lg w-100">
            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <img src="uniceplac2.png" alt="Logo UNICEPLAC" style="max-height: 100px;" class="mb-3">
                    <h6 class="text-uniceplac fw-bold tracking-tight">CENTRAL DE RESERVAS ACADÊMICAS</h6>
                </div>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success py-2 small text-center"><?= $sucesso ?></div>
                <?php endif; ?>

                <?php if ($erro): ?>
                    <div class="alert alert-danger py-2 small text-center"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <form action="index.php" method="POST">
                    <?php if (!empty($redirect)): ?>
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-bold text-uniceplac">E-mail</label>
                        <input type="email" class="form-control" name="email" id="email" required placeholder="seu@email.com">
                    </div>

                    <div class="mb-3">
                        <label for="senha" class="form-label small fw-bold text-uniceplac">Senha</label>
                        <input type="password" class="form-control" name="senha" id="senha" required>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="lembrar_me" value="1" id="lembrar_me" checked>
                        <label class="form-check-label small text-muted" for="lembrar_me">
                            Manter conectado neste dispositivo (recomendado)
                        </label>
                    </div>

                    <button type="submit" id="btnAcessar" class="btn btn-uniceplac w-100 py-2">Acessar Sistema</button>
                    <div class="text-end mt-2">
                        <a href="esqueci_senha.php" class="small text-decoration-none text-muted">Esqueci minha senha</a>
                    </div>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="mb-0 text-muted small">
                        Ainda não possui acesso? Solicite ao <strong>coordenador</strong> o cadastro do seu e-mail.
                    </p>
                </div>
            </div>
        </div>
    </main>
    <script>
        document.querySelector('form[action="index.php"]')?.addEventListener('submit', function () {
            const btn = document.getElementById('btnAcessar');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Entrando...';
            }
        });
    </script>
</body>
</html>
