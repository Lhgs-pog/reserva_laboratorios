<?php
/**
 * Suporte Panel View
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Suporte - LabHub UNICEPLAC Laravel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark" style="background-color: #00734F;">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">LabHub - Suporte</span>
            <span class="text-white">Olá, <?= $usuario->nome ?? 'Usuário' ?></span>
            <a href="/logout" class="btn btn-sm btn-outline-light">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h4>Chamados Pendentes (<?= count($chamados) ?>)</h4>
        
        <?php if (empty($chamados)): ?>
            <p class="alert alert-success">Nenhum chamado pendente</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($chamados as $chamado): ?>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><?= $chamado->getAttribute('titulo') ?></h6>
                            <p class="card-text"><?= substr($chamado->getAttribute('descricao'), 0, 100) ?>...</p>
                            <small class="text-muted"><?= $chamado->getAttribute('data_criacao') ?></small>
                            <button class="btn btn-sm btn-success float-end">Resolver</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
