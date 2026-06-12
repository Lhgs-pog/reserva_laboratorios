<?php
/**
 * Cadastro View
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - LabHub UNICEPLAC Laravel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow" style="width: 28rem;">
        <div class="card-body p-4">
            <h5 class="card-title fw-bold mb-4">Cadastro - LabHub UNICEPLAC</h5>

            <?php if ($mensagem): ?>
                <div class="alert alert-info"><?= $mensagem ?></div>
            <?php endif; ?>

            <form action="/cadastro" method="POST">
                <div class="mb-3">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" name="nome" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">E-mail Institucional</label>
                    <input type="email" class="form-control" name="email" placeholder="seu@uniceplac.edu.br" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input type="password" class="form-control" name="senha" minlength="8" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirmar Senha</label>
                    <input type="password" class="form-control" name="confirmar_senha" minlength="8" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Finalizar Cadastro</button>

                <div class="text-center mt-3">
                    <p class="small text-muted">Já tem conta? <a href="/login">Fazer login</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
