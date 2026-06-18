<div id="sessao-cursos" class="content-section">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="border-top: 4px solid var(--roxo-uniceplac);">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-mortarboard text-primary me-2"></i> Novo
                            Curso</h5>
                    </div>
                    <div class="card-body bg-light">
                        <form method="POST" action="painel_coordenador.php#sessao-cursos">
                            <div class="mb-3"><label class="form-label small fw-bold">Nome do Curso:</label><input
                                    type="text" name="nome_curso" class="form-control" required
                                    placeholder="Ex: Engenharia"></div>
                            <button type="submit" name="salvar_curso" class="btn btn-primary w-100 fw-bold">Salvar
                                Curso</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark">Cursos Cadastrados</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Nome</th>
                                        <th class="text-end pe-4">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cursos_cadastrados as $c): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold">
                                                <?= htmlspecialchars($c['nome']) ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                                    data-bs-target="#editCurso<?= $c['id'] ?>"><i
                                                        class="bi bi-pencil"></i></button>
                                                <form method="POST" action="painel_coordenador.php#sessao-cursos"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Deseja excluir este curso?');"><input
                                                        type="hidden" name="id_curso" value="<?= $c['id'] ?>"><button
                                                        type="submit" name="excluir_curso"
                                                        class="btn btn-sm btn-outline-danger"><i
                                                            class="bi bi-trash"></i></button></form>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="editCurso<?= $c['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title fw-bold">Editar Curso</h6><button
                                                            type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <form method="POST" action="painel_coordenador.php#sessao-cursos">
                                                            <input type="hidden" name="id_curso"
                                                                value="<?= $c['id'] ?>"><label
                                                                class="form-label small fw-bold">Nome do
                                                                Curso:</label><input type="text" name="nome_curso"
                                                                class="form-control mb-3"
                                                                value="<?= htmlspecialchars($c['nome']) ?>" required><button
                                                                type="submit" name="editar_curso"
                                                                class="btn btn-primary w-100">Atualizar</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>