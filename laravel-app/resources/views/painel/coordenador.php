<?php
/**
 * Coordenador Panel View
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Coordenador - LabHub UNICEPLAC Laravel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark" style="background-color: #00734F;">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">LabHub - Coordenador</span>
            <span class="text-white">Olá, <?= $usuario->nome ?? 'Usuário' ?></span>
            <a href="/logout" class="btn btn-sm btn-outline-light">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <h5>Solicitações Pendentes (<?= count($solicitacoes) ?>)</h5>
                <?php if (empty($solicitacoes)): ?>
                    <p class="alert alert-info">Nenhuma solicitação pendente</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($solicitacoes as $item): ?>
                        <li class="list-group-item">
                            <strong><?= $item->getAttribute('professor') ?></strong> - <?= $item->getAttribute('data_reserva') ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <h5>Reservas Confirmadas (<?= count($confirmados) ?>)</h5>
                <?php if (empty($confirmados)): ?>
                    <p class="alert alert-info">Nenhuma reserva confirmada</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($confirmados as $item): ?>
                        <li class="list-group-item">
                            <strong><?= $item->getAttribute('professor') ?></strong> - <?= $item->getAttribute('data_reserva') ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
