<div id="sessao-locais" class="content-section">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="border-top: 4px solid #6c757d;">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-building text-secondary me-2"></i> Blocos
                        </h5>
                    </div>
                    <div class="card-body bg-light">
                        <form method="POST" action="painel_coordenador.php#sessao-locais" class="d-flex mb-3"><input
                                type="text" name="nome_bloco" class="form-control me-2" required
                                placeholder="Novo bloco..."><button type="submit" name="salvar_bloco"
                                class="btn btn-secondary"><i class="bi bi-plus-lg"></i></button></form>
                        <ul class="list-group" style="max-height: 250px; overflow-y: auto;">
                            <?php foreach ($blocos_cadastrados as $b): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center"><span
                                        class="small fw-bold">
                                        <?= htmlspecialchars($b['nome']) ?>
                                    </span>
                                    <div><button class="btn btn-sm text-primary p-0 me-2" data-bs-toggle="modal"
                                            data-bs-target="#editBloco<?= $b['id'] ?>"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="painel_coordenador.php#sessao-locais" class="d-inline"
                                            onsubmit="return confirm('Excluir?');"><input type="hidden" name="id_bloco"
                                                value="<?= $b['id'] ?>"><button type="submit" name="excluir_bloco"
                                                class="btn btn-sm text-danger border-0 p-0"><i
                                                    class="bi bi-trash"></i></button></form>
                                    </div>
                                </li>
                                <div class="modal fade" id="editBloco<?= $b['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6>Editar Bloco</h6><button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <form method="POST" action="painel_coordenador.php#sessao-locais"><input
                                                        type="hidden" name="id_bloco" value="<?= $b['id'] ?>"><input
                                                        type="text" name="nome_bloco" class="form-control mb-3"
                                                        value="<?= htmlspecialchars($b['nome']) ?>" required><button
                                                        type="submit" name="editar_bloco"
                                                        class="btn btn-secondary w-100">Atualizar</button></form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="border-top: 4px solid #198754;">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-layers text-success me-2"></i> Andares
                        </h5>
                    </div>
                    <div class="card-body bg-light">
                        <form method="POST" action="painel_coordenador.php#sessao-locais" class="d-flex mb-3"><input
                                type="text" name="nome_andar" class="form-control me-2" required
                                placeholder="Novo andar..."><button type="submit" name="salvar_andar"
                                class="btn btn-success"><i class="bi bi-plus-lg"></i></button></form>
                        <ul class="list-group" style="max-height: 250px; overflow-y: auto;">
                            <?php foreach ($andares_cadastrados as $a): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center"><span
                                        class="small fw-bold">
                                        <?= htmlspecialchars($a['nome']) ?>
                                    </span>
                                    <div><button class="btn btn-sm text-primary p-0 me-2" data-bs-toggle="modal"
                                            data-bs-target="#editAndar<?= $a['id'] ?>"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="painel_coordenador.php#sessao-locais" class="d-inline"
                                            onsubmit="return confirm('Excluir?');"><input type="hidden" name="id_andar"
                                                value="<?= $a['id'] ?>"><button type="submit" name="excluir_andar"
                                                class="btn btn-sm text-danger border-0 p-0"><i
                                                    class="bi bi-trash"></i></button></form>
                                    </div>
                                </li>
                                <div class="modal fade" id="editAndar<?= $a['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6>Editar Andar</h6><button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <form method="POST" action="painel_coordenador.php#sessao-locais"><input
                                                        type="hidden" name="id_andar" value="<?= $a['id'] ?>"><input
                                                        type="text" name="nome_andar" class="form-control mb-3"
                                                        value="<?= htmlspecialchars($a['nome']) ?>" required><button
                                                        type="submit" name="editar_andar"
                                                        class="btn btn-success w-100">Atualizar</button></form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="border-top: 4px solid #0dcaf0;">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-door-open text-info me-2"></i> Salas</h5>
                    </div>
                    <div class="card-body bg-light">
                        <form method="POST" action="painel_coordenador.php#sessao-locais" class="d-flex mb-3"><input
                                type="text" name="nome_sala" class="form-control me-2" required
                                placeholder="Nova sala..."><button type="submit" name="salvar_sala"
                                class="btn btn-info text-white"><i class="bi bi-plus-lg"></i></button></form>
                        <ul class="list-group" style="max-height: 250px; overflow-y: auto;">
                            <?php foreach ($salas_cadastradas as $s): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center"><span
                                        class="small fw-bold">
                                        <?= htmlspecialchars($s['nome']) ?>
                                    </span>
                                    <div><button class="btn btn-sm text-primary p-0 me-2" data-bs-toggle="modal"
                                            data-bs-target="#editSala<?= $s['id'] ?>"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="painel_coordenador.php#sessao-locais" class="d-inline"
                                            onsubmit="return confirm('Excluir?');"><input type="hidden" name="id_sala"
                                                value="<?= $s['id'] ?>"><button type="submit" name="excluir_sala"
                                                class="btn btn-sm text-danger border-0 p-0"><i
                                                    class="bi bi-trash"></i></button></form>
                                    </div>
                                </li>
                                <div class="modal fade" id="editSala<?= $s['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6>Editar Sala</h6><button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <form method="POST" action="painel_coordenador.php#sessao-locais"><input
                                                        type="hidden" name="id_sala" value="<?= $s['id'] ?>"><input
                                                        type="text" name="nome_sala" class="form-control mb-3"
                                                        value="<?= htmlspecialchars($s['nome']) ?>" required><button
                                                        type="submit" name="editar_sala"
                                                        class="btn btn-info text-white w-100">Atualizar</button></form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>