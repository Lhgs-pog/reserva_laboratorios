<?php
if (!function_exists('lh_render_bloco_select')) {
    function lh_render_bloco_select(string $name, ?string $selected = '', bool $required = false): string
    {
        global $blocos_cadastrados;
        $req = $required ? ' required' : '';
        $html = '<select name="' . htmlspecialchars($name) . '" class="form-select" data-lh-combobox data-lh-create="blocos" data-lh-value="nome"' . $req . '>';
        $html .= '<option value="">Selecione o bloco...</option>';
        $found = false;
        foreach ($blocos_cadastrados as $b) {
            $nome = $b['nome'];
            $sel = ($selected !== '' && $selected === $nome) ? ' selected' : '';
            if ($sel) {
                $found = true;
            }
            $html .= '<option value="' . htmlspecialchars($nome) . '"' . $sel . '>' . htmlspecialchars($nome) . '</option>';
        }
        if ($selected !== '' && !$found) {
            $html .= '<option value="' . htmlspecialchars($selected) . '" selected>' . htmlspecialchars($selected) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    function lh_render_andar_select(string $name, ?string $selected = '', bool $required = false): string
    {
        global $andares_cadastrados;
        $req = $required ? ' required' : '';
        $html = '<select name="' . htmlspecialchars($name) . '" class="form-select" data-lh-combobox data-lh-create="andares" data-lh-value="nome"' . $req . '>';
        $html .= '<option value="">Selecione o andar...</option>';
        $found = false;
        foreach ($andares_cadastrados as $a) {
            $nome = $a['nome'];
            $sel = ($selected !== '' && $selected === $nome) ? ' selected' : '';
            if ($sel) {
                $found = true;
            }
            $html .= '<option value="' . htmlspecialchars($nome) . '"' . $sel . '>' . htmlspecialchars($nome) . '</option>';
        }
        if ($selected !== '' && !$found) {
            $html .= '<option value="' . htmlspecialchars($selected) . '" selected>' . htmlspecialchars($selected) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
}
?>
<div id="sessao-labs" class="content-section">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="border-top: 4px solid var(--info);">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-circle text-uniceplac me-2"></i> Novo
                            Laboratório</h5>
                    </div>
                    <div class="card-body bg-light">
                        <form method="POST" action="painel_coordenador.php#sessao-labs">
                            <div class="mb-3"><label class="form-label small fw-bold">Nome do Lab:</label><input
                                    type="text" name="nome_lab" class="form-control" required
                                    placeholder="Ex: Lab de Redes"></div>
                            <div class="mb-3"><label class="form-label small fw-bold">Bloco
                                    (Opcional):</label><?= lh_render_bloco_select('localizacao_lab') ?></div>
                            <div class="mb-3"><label class="form-label small fw-bold">Andar
                                    (Opcional):</label><?= lh_render_andar_select('andar_lab') ?></div>
                            <div class="mb-3"><label class="form-label small fw-bold">Capacidade
                                    (Lugares):</label><input type="number" name="capacidade_lab" class="form-control"
                                    required></div>
                            <button type="submit" name="salvar_lab" class="btn btn-uniceplac w-100 fw-bold">Salvar
                                Laboratório</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-pc-display text-secondary me-2"></i>
                            Laboratórios Ativos</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-4">Nome</th>
                                        <th>Localização</th>
                                        <th>Capacidade</th>
                                        <th class="pe-4 text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($laboratorios_cadastrados as $lab): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark">
                                                <?= htmlspecialchars($lab['nome']) ?>
                                            </td>
                                            <td><span class="small text-muted">
                                                    <?= htmlspecialchars($lab['localizacao'] ?? '-') ?>
                                                    <br> Andar:
                                                    <?= htmlspecialchars($lab['andar'] ?? '-') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= $lab['capacidade'] ?> vagas
                                            </td>
                                            <td class="pe-4 text-end">
                                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                                    data-bs-target="#editLab<?= $lab['id'] ?>"><i
                                                        class="bi bi-pencil"></i></button>
                                                <form method="POST" action="painel_coordenador.php#sessao-labs"
                                                    class="d-inline" onsubmit="return confirm('Deseja excluir este lab?');">
                                                    <input type="hidden" name="id_lab" value="<?= $lab['id'] ?>"><button
                                                        type="submit" name="excluir_lab"
                                                        class="btn btn-sm btn-outline-danger"><i
                                                            class="bi bi-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="editLab<?= $lab['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title fw-bold">Editar Laboratório</h6><button
                                                            type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <form method="POST" action="painel_coordenador.php#sessao-labs">
                                                            <input type="hidden" name="id_lab" value="<?= $lab['id'] ?>">
                                                            <div class="mb-3"><label class="form-label small fw-bold">Nome
                                                                    do
                                                                    Lab:</label><input type="text" name="nome_lab"
                                                                    class="form-control"
                                                                    value="<?= htmlspecialchars($lab['nome']) ?>" required>
                                                            </div>
                                                            <div class="mb-3"><label
                                                                    class="form-label small fw-bold">Bloco:</label><?= lh_render_bloco_select('localizacao_lab', $lab['localizacao'] ?? '') ?></div>
                                                            <div class="mb-3"><label
                                                                    class="form-label small fw-bold">Andar:</label><?= lh_render_andar_select('andar_lab', $lab['andar'] ?? '') ?></div>
                                                            <div class="mb-4"><label
                                                                    class="form-label small fw-bold">Capacidade:</label><input
                                                                    type="number" name="capacidade_lab" class="form-control"
                                                                    value="<?= $lab['capacidade'] ?>" required></div>
                                                            <button type="submit" name="editar_lab"
                                                                class="btn btn-uniceplac w-100 fw-bold">Atualizar
                                                                Laboratório</button>
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
