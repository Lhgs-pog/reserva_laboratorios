<?php
// ============================================================
// Verificação de e-mail — MVC via router
// ============================================================

$controller_data = require __DIR__ . '/app/router.php';

if (is_array($controller_data)) {
    extract($controller_data);
} else {
    $mensagem = 'Erro ao processar verificação.';
    $tipo_alerta = 'danger';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Conta — LabHub UNICEPLAC</title>
    <?php require __DIR__ . '/app/Views/partials/favicon.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --verde-uniceplac: #00734F; }
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .card-verify { max-width: 420px; border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .btn-uniceplac { background: var(--verde-uniceplac); color: #fff; font-weight: 700; border: none; }
        .btn-uniceplac:hover { background: #005a3e; color: #fff; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="card card-verify w-100">
        <div class="card-body p-4 p-md-5 text-center">
            <?php if (($tipo_alerta ?? '') === 'success'): ?>
                <div class="display-4 text-success mb-3"><i class="bi bi-check-circle-fill"></i></div>
                <h1 class="h4 fw-bold mb-3">E-mail confirmado!</h1>
            <?php elseif (($tipo_alerta ?? '') === 'warning'): ?>
                <div class="display-4 text-warning mb-3"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <h1 class="h4 fw-bold mb-3">Atenção</h1>
            <?php else: ?>
                <div class="display-4 text-danger mb-3"><i class="bi bi-x-circle-fill"></i></div>
                <h1 class="h4 fw-bold mb-3">Não foi possível confirmar</h1>
            <?php endif; ?>

            <div class="alert alert-<?= htmlspecialchars($tipo_alerta ?? 'danger') ?> text-start mb-4" role="alert">
                <?= $mensagem ?? '' ?>
            </div>

            <?php if (($tipo_alerta ?? '') === 'success'): ?>
                <p class="text-muted small mb-4">Use a senha que você recebeu da coordenação ou definiu pelo link enviado.</p>
            <?php endif; ?>

            <a href="index.php" class="btn btn-uniceplac w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Ir para o login
            </a>
        </div>
    </div>
</body>
</html>
