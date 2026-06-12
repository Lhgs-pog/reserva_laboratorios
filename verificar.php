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
    $mensagem = "Erro ao processar verificação.";
    $tipo_alerta = "danger";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Verificação de Conta - Sistema de Laboratórios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow text-center" style="width: 30rem;">
            <div class="card-body p-5">
                
                <?php if ($tipo_alerta == 'success'): ?>
                    <h1 class="display-1 text-success mb-3">✓</h1>
                    <h3 class="card-title mb-4">Conta Ativada!</h3>
                <?php elseif ($tipo_alerta == 'warning'): ?>
                    <h1 class="display-1 text-warning mb-3">!</h1>
                    <h3 class="card-title mb-4">Atenção</h3>
                <?php else: ?>
                    <h1 class="display-1 text-danger mb-3">✗</h1>
                    <h3 class="card-title mb-4">Erro na Verificação</h3>
                <?php endif; ?>

                <div class="alert alert-<?= $tipo_alerta ?>" role="alert">
                    <?= $mensagem ?>
                </div>

                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary w-100">Ir para a página de Login</a>
                </div>
                
            </div>
        </div>
    </div>
</body>
</html>