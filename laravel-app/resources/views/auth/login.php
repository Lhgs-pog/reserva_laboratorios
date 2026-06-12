<?php
/**
 * Login View
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LabHub UNICEPLAC Laravel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --azul-uniceplac: #00734F;
            --amarelo-uniceplac: #f07f3c;
        }
        body { background-color: #f0f2f5; }
        .btn-uniceplac { background-color: var(--azul-uniceplac); color: white; }
        .btn-uniceplac:hover { background-color: #045238; color: var(--amarelo-uniceplac); }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg" style="width: 28rem;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <h6 class="fw-bold" style="color: var(--azul-uniceplac);">CENTRAL DE RESERVAS - LARAVEL</h6>
            </div>

            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= $sucesso ?></div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= $erro ?></div>
            <?php endif; ?>

            <form action="/login/store" method="POST">
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" class="form-control" name="email" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Senha</label>
                    <input type="password" class="form-control" name="senha" required>
                </div>

                <button type="submit" class="btn btn-uniceplac w-100 py-2">Acessar</button>

                <div class="text-center mt-4">
                    <p class="mb-1 text-muted small">Novo usuário?</p>
                    <a href="/cadastro" class="text-decoration-none small fw-bold" style="color: var(--amarelo-uniceplac);">Cadastrar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
