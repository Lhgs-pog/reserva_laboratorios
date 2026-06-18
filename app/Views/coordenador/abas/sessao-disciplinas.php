<div id="sessao-disciplinas" class="content-section">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="border-top: 4px solid var(--laranja-uniceplac);">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-book-half text-primary me-2"></i> Nova
                            Disciplina</h5>
                    </div>
                    <div class="card-body bg-light">
                        <form method="POST" action="painel_coordenador.php#sessao-disciplinas">
                            <div class="mb-3"><label class="form-label small fw-bold">Nome da
                                    Disciplina:</label><input type="text" name="nome_disciplina" class="form-control"
                                    required placeholder="Ex: Algoritmos"></div>
                            <button type="submit" name="salvar_disciplina" class="btn btn-primary w-100 fw-bold">Salvar
                                Disciplina</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark">Disciplinas Cadastradas</h5>
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
                                    <?php foreach ($disciplinas as $d): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold">
                                                <?= htmlspecialchars($d['nome']) ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                                    data-bs-target="#editDisciplina<?= $d['id'] ?>"><i
                                                        class="bi bi-pencil"></i></button>
                                                <form method="POST" action="painel_coordenador.php#sessao-disciplinas"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Deseja excluir esta disciplina?');"><input
                                                        type="hidden" name="id_disciplina" value="<?= $d['id'] ?>"><button
                                                        type="submit" name="excluir_disciplina"
                                                        class="btn btn-sm btn-outline-danger"><i
                                                            class="bi bi-trash"></i></button></form>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="editDisciplina<?= $d['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title fw-bold">Editar Disciplina</h6><button
                                                            type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <form method="POST"
                                                            action="painel_coordenador.php#sessao-disciplinas"><input
                                                                type="hidden" name="id_disciplina"
                                                                value="<?= $d['id'] ?>"><label
                                                                class="form-label small fw-bold">Nome da
                                                                Disciplina:</label><input type="text" name="nome_disciplina"
                                                                class="form-control mb-3"
                                                                value="<?= htmlspecialchars($d['nome']) ?>" required><button
                                                                type="submit" name="editar_disciplina"
                                                                class="btn btn-primary w-100">Atualizar</button></form>
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