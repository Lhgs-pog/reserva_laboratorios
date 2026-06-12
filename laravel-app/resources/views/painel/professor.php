<?php
/**
 * Professor Panel View
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Professor - LabHub UNICEPLAC Laravel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark" style="background-color: #00734F;">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">LabHub - Professor</span>
            <span class="text-white">Olá, <?= $usuario->nome ?? 'Usuário' ?></span>
            <a href="/logout" class="btn btn-sm btn-outline-light">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h4>Meus Agendamentos</h4>
                
                <?php if (empty($alocacoes)): ?>
                    <p class="alert alert-info">Você não tem agendamentos</p>
                <?php else: ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Laboratório</th>
                                <th>Disciplina</th>
                                <th>Data</th>
                                <th>Turno</th>
                                <th>Período</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alocacoes as $alocacao): ?>
                            <tr>
                                <td><?= $alocacao->getAttribute('laboratorio') ?></td>
                                <td><?= $alocacao->getAttribute('disciplina') ?></td>
                                <td><?= $alocacao->getAttribute('data_reserva') ?></td>
                                <td><?= $alocacao->getAttribute('turno') ?></td>
                                <td><?= $alocacao->getAttribute('periodo') ?></td>
                                <td>
                                    <span class="badge bg-success"><?= $alocacao->getAttribute('status') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
