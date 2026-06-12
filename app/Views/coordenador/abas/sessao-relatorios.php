<?php
if (!function_exists('renderCellProf')) {
    function renderCellProf($t, $l)
    {
        if ($t == 0) {
            return '<td class="text-muted opacity-25">-</td>';
        }
        $s = $t - $l;
        $html = '<td><div class="fw-bold text-dark">' . $t . 'h</div><div style="font-size:0.75rem; line-height:1; margin-top:2px;">';
        if ($l > 0) {
            $html .= '<span class="text-danger">' . $l . 'L</span> ';
        }
        if ($s > 0) {
            $html .= '<span class="text-success">' . $s . 'S</span>';
        }
        $html .= '</div></td>';
        return $html;
    }
}
?>
<div id="sessao-relatorios" class="content-section">
            <?php if (!$quadro_selecionado): ?>
                <div class="alert alert-warning text-center py-5 shadow-sm border-0" style="border-radius: 12px;"><i
                        class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i><strong>Atenção:</strong> Você precisa
                    selecionar um "Quadro de Horários" na aba Editor para gerar os relatórios.</div>
            <?php else: ?>
                <div class="alert alert-light border shadow-sm mb-4 py-3">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <span class="badge bg-uniceplac px-3 py-2"><i class="bi bi-table me-1"></i> Quadro: <?= htmlspecialchars($nome_quadro_selecionado ?: 'Grade ativa') ?></span>
                        <span class="badge bg-primary px-3 py-2"><i class="bi bi-journal-bookmark me-1"></i> <?= (int) $total_aulas_grade ?> aulas na grade</span>
                        <span class="badge bg-info text-dark px-3 py-2"><i class="bi bi-calendar-plus me-1"></i> <?= (int) $total_reservas_avulsas ?> reservas avulsas (<?= (int) $total_reservas_avulsas_horas ?>h em labs)</span>
                        <a href="javascript:void(0);" onclick="showSection('sessao-quadro-horario')" class="btn btn-sm btn-outline-success ms-auto">
                            <i class="bi bi-pencil-square me-1"></i> Editar grade / vincular labs
                        </a>
                    </div>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                    <div>
                        <h4 class="text-uniceplac fw-bold mb-0"><i class="bi bi-graph-up-arrow me-2"></i> Dashboard
                            Interativo</h4>
                        <p class="text-muted small mb-0">Métricas da grade fixa + reservas avulsas aprovadas. <span class="fw-bold">Clique
                                nos gráficos para filtrar as tabelas abaixo.</span></p>
                    </div>
                    <div class="d-flex gap-2 mt-2 mt-md-0">
                        <button class="btn btn-outline-success" onclick="exportarDashboardCSV()">
                            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Exportar CSV
                        </button>
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i>Imprimir
                        </button>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 bg-white"
                            style="border-left: 4px solid var(--verde-uniceplac);">
                            <div class="card-body p-3">
                                <h6 class="text-muted text-uppercase fw-bold small mb-1">Ocupação Global (Labs)</h6>
                                <h2 class="fw-bold text-dark mb-2"><?= $taxa_ocupacao_global ?>%</h2>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-success" style="width: <?= $taxa_ocupacao_global ?>%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 bg-white"
                            style="border-left: 4px solid var(--laranja-uniceplac);">
                            <div class="card-body p-3">
                                <h6 class="text-muted text-uppercase fw-bold small mb-1">Ociosidade Global (Labs)</h6>
                                <h2 class="fw-bold text-danger mb-2"><?= $taxa_ociosidade_global ?>%</h2>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-danger" style="width: <?= $taxa_ociosidade_global ?>%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 bg-white" style="border-left: 4px solid var(--info);">
                            <div class="card-body p-3">
                                <h6 class="text-muted text-uppercase fw-bold small mb-1">Lab Mais Ocioso</h6>
                                <h4 class="fw-bold text-dark mb-0 text-truncate"
                                    title="<?= htmlspecialchars($lab_mais_ocioso['nome']) ?>">
                                    <?= htmlspecialchars($lab_mais_ocioso['nome']) ?>
                                </h4>
                                <small class="text-danger fw-bold"><?= $lab_mais_ocioso['horas'] ?>h Livres na
                                    semana</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 bg-white"
                            style="border-left: 4px solid var(--roxo-uniceplac);">
                            <div class="card-body p-3">
                                <h6 class="text-muted text-uppercase fw-bold small mb-1">Lab Mais Utilizado</h6>
                                <h4 class="fw-bold text-dark mb-0 text-truncate"
                                    title="<?= htmlspecialchars($lab_mais_usado['nome']) ?>">
                                    <?= htmlspecialchars($lab_mais_usado['nome']) ?>
                                </h4>
                                <small class="text-success fw-bold"><?= $lab_mais_usado['horas'] ?>h Ocupadas na
                                    semana</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="apple-search-box shadow-sm">
                        <i class="bi bi-search text-muted ms-2 fs-5"></i>
                        <input type="text" id="filtroDashInput" class="apple-search-input fs-5 ms-2"
                            placeholder="Pesquise por professor ou laboratório...">
                        <i class="bi bi-x-circle-fill text-muted me-2 fs-5" id="btnLimparFiltroDash"
                            style="cursor: pointer; display: none;" title="Limpar Filtro"></i>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                            <div class="card-header bg-white border-0 pt-4 pb-0 text-center">
                                <h6 class="fw-bold text-dark mb-0">Perfil de Ensino (Top 10 Professores)</h6>
                                <small class="text-muted">Horas em Sala (Verde) vs Horas no Lab (Vermelho)</small>
                            </div>
                            <div class="card-body" style="position: relative; height: 300px;">
                                <canvas id="chartPerfilAulas"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                            <div class="card-header bg-white border-0 pt-4 pb-0 text-center">
                                <h6 class="fw-bold text-dark mb-0">Demanda de Infraestrutura por Curso</h6>
                                <small class="text-muted">Volume total de horas consumidas na grade</small>
                            </div>
                            <div class="card-body"
                                style="position: relative; height: 300px; display: flex; justify-content: center;">
                                <canvas id="chartCursos"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold text-dark mb-3 mt-5"><i class="bi bi-battery-half text-warning me-2"></i> Raio-X de
                    Ociosidade dos Laboratórios</h5>
                <div class="row g-3 mb-4">
                    <?php
                    if (count($relatorio_labs) > 0):
                        foreach ($relatorio_labs as $rl):
                            $ocioso = $capacidade_max_semanal - $rl['total'];
                            $pct = round(($rl['total'] / $capacidade_max_semanal) * 100);
                            ?>
                            <div class="col-md-3 card-ociosidade" data-search="<?= strtolower($rl['laboratorio']) ?>">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold text-dark mb-1 text-truncate"
                                            title="<?= htmlspecialchars($rl['laboratorio']) ?>">
                                            <?= htmlspecialchars($rl['laboratorio']) ?>
                                        </h6>
                                        <div class="d-flex justify-content-between small mb-2">
                                            <span class="text-danger fw-bold">Uso: <?= $rl['total'] ?>h</span>
                                            <span class="text-success fw-bold">Livre: <?= $ocioso ?>h</span>
                                        </div>
                                        <div class="progress bg-success bg-opacity-25" style="height: 6px;">
                                            <div class="progress-bar bg-danger" style="width: <?= $pct ?>%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; else:
                        echo '<div class="col-12"><div class="alert alert-light border text-center text-muted">Sem dados de laboratórios.</div></div>';
                    endif; ?>
                </div>

                <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid #0dcaf0; border-radius: 12px;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                        onclick="abrirSanfona('collapseTabelaBI', 'setaTabelaBI')" style="cursor:pointer;">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-list-columns text-info me-2"></i> Relatório
                            Analítico: Onde estão os professores?</h5>
                        <i class="bi bi-chevron-up text-muted transition-transform" id="setaTabelaBI"
                            style="transform: rotate(180deg);"></i>
                    </div>
                    <div id="collapseTabelaBI" style="display: block;">
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0 text-center">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="text-start ps-4">Professor</th>
                                            <th>Disciplina</th>
                                            <th>Localização</th>
                                            <th>Dia / Turno</th>
                                            <th>Carga Total</th>
                                            <th class="text-danger">Horas Lab</th>
                                            <th class="text-success">Horas Sala</th>
                                            <th class="pe-4 text-start">Alerta / Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="corpoTabelaBI">
                                        <?php if (count($todas_aulas) > 0): ?>
                                            <?php foreach ($todas_aulas as $aula):
                                                $ch_t = $aula['carga_horaria_total'] ?? 2;
                                                $ch_l = $aula['horas_laboratorio'] ?? 0;
                                                $ch_s = $ch_t - $ch_l;
                                                $badge = '<span class="badge bg-secondary">Teórica (Sala)</span>';
                                                if ($ch_l > 0 && $ch_l == $ch_t) {
                                                    $badge = '<span class="badge bg-primary">Prática (100% Lab)</span>';
                                                } elseif ($ch_l > 0 && $ch_l < $ch_t) {
                                                    $badge = '<span class="badge bg-warning text-dark"><i class="bi bi-arrow-left-right me-1"></i> Transição Lab ➔ Sala</span>';
                                                }
                                                ?>
                                                <tr class="linha-bi">
                                                    <td class="text-start ps-4 fw-bold text-dark"
                                                        data-search="<?= strtolower($aula['prof_nome'] ?? '') ?>">
                                                        <?= htmlspecialchars($aula['prof_nome'] ?? 'EAD') ?>
                                                    </td>
                                                    <td><small
                                                            class="text-muted"><?= htmlspecialchars($aula['disc_nome']) ?></small>
                                                    </td>
                                                    <td data-search="<?= strtolower($aula['lab_nome'] ?? '') ?>">
                                                        <?php if ($aula['lab_nome']): ?><span class="text-primary fw-bold"><i
                                                                    class="bi bi-pc-display me-1"></i><?= htmlspecialchars($aula['lab_nome']) ?></span>
                                                        <?php else: ?><span class="text-success"><i
                                                                    class="bi bi-door-open me-1"></i>Sala
                                                                <?= htmlspecialchars($aula['sala'] ?? '-') ?></span><?php endif; ?>
                                                    </td>
                                                    <td><?= $aula['dia_semana'] ?> <br> <small
                                                            class="text-muted"><?= $aula['turno'] ?></small></td>
                                                    <td class="fw-bold"><?= $ch_t ?>h</td>
                                                    <td class="text-danger fw-bold"><?= $ch_l > 0 ? $ch_l . 'h' : '-' ?></td>
                                                    <td class="text-success fw-bold"><?= $ch_s > 0 ? $ch_s . 'h' : '-' ?></td>
                                                    <td class="pe-4 text-start"><?= $badge ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-5 text-muted">Nenhuma aula alocada neste
                                                    quadro.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4"
                    style="border-top: 4px solid var(--roxo-uniceplac); border-radius: 12px;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                        onclick="abrirSanfona('collapseProfTabela', 'setaProfTabela')" style="cursor:pointer;">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-person-video3 text-primary me-2"></i> Relatório
                            Diário de Professores (Lab vs Sala)</h5>
                        <i class="bi bi-chevron-up text-muted transition-transform" id="setaProfTabela"
                            style="transform: rotate(180deg);"></i>
                    </div>
                    <div id="collapseProfTabela" style="display: block;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-center" id="tabelaProfessores">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start ps-4">Professor</th>
                                            <th>Seg</th>
                                            <th>Ter</th>
                                            <th>Qua</th>
                                            <th>Qui</th>
                                            <th>Sex</th>
                                            <th>Sáb</th>
                                            <th class="pe-4 bg-light text-primary border-start">TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($relatorio_professores) > 0): ?>
                                            <?php foreach ($relatorio_professores as $rp): ?>
                                                <tr class="linha-filtro">
                                                    <td class="text-start ps-4 fw-bold text-dark"
                                                        data-search="<?= strtolower($rp['professor'] ?? '') ?>">
                                                        <?= htmlspecialchars($rp['professor']) ?>
                                                    </td>
                                                    <?= renderCellProf($rp['seg_t'], $rp['seg_l']) ?>
                                                    <?= renderCellProf($rp['ter_t'], $rp['ter_l']) ?>
                                                    <?= renderCellProf($rp['qua_t'], $rp['qua_l']) ?>
                                                    <?= renderCellProf($rp['qui_t'], $rp['qui_l']) ?>
                                                    <?= renderCellProf($rp['sex_t'], $rp['sex_l']) ?>
                                                    <?= renderCellProf($rp['sab_t'], $rp['sab_l']) ?>
                                                    <td class="pe-4 bg-light fw-bold fs-5 text-primary border-start">
                                                        <?= $rp['total'] ?>h<br>
                                                        <small class="fs-6 fw-normal"><span
                                                                class="text-danger"><?= $rp['total_l'] ?>L</span> | <span
                                                                class="text-success"><?= $rp['total'] - $rp['total_l'] ?>S</span></small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">Nenhum professor alocado
                                                    neste quadro.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--info); border-radius: 12px;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                        onclick="abrirSanfona('collapseLabTabela', 'setaLabTabela')" style="cursor:pointer;">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-pc-display text-info me-2"></i> Relatório Diário
                            de Ocupação de Laboratórios</h5>
                        <i class="bi bi-chevron-up text-muted transition-transform" id="setaLabTabela"
                            style="transform: rotate(180deg);"></i>
                    </div>
                    <div id="collapseLabTabela" style="display: block;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-center" id="tabelaLabs">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start ps-4">Laboratório</th>
                                            <th>Seg</th>
                                            <th>Ter</th>
                                            <th>Qua</th>
                                            <th>Qui</th>
                                            <th>Sex</th>
                                            <th>Sáb</th>
                                            <th class="pe-4 bg-light text-info border-start">TOTAL Ocupado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($relatorio_labs) > 0): ?>
                                            <?php foreach ($relatorio_labs as $rl): ?>
                                                <tr class="linha-filtro">
                                                    <td class="text-start ps-4 fw-bold text-dark"
                                                        data-search="<?= strtolower($rl['laboratorio']) ?>">
                                                        <?= htmlspecialchars($rl['laboratorio']) ?>
                                                    </td>
                                                    <td
                                                        class="<?= $rl['seg'] > 0 ? 'text-danger fw-bold' : 'text-muted opacity-25' ?>">
                                                        <?= $rl['seg'] ?: '-' ?>
                                                    </td>
                                                    <td
                                                        class="<?= $rl['ter'] > 0 ? 'text-danger fw-bold' : 'text-muted opacity-25' ?>">
                                                        <?= $rl['ter'] ?: '-' ?>
                                                    </td>
                                                    <td
                                                        class="<?= $rl['qua'] > 0 ? 'text-danger fw-bold' : 'text-muted opacity-25' ?>">
                                                        <?= $rl['qua'] ?: '-' ?>
                                                    </td>
                                                    <td
                                                        class="<?= $rl['qui'] > 0 ? 'text-danger fw-bold' : 'text-muted opacity-25' ?>">
                                                        <?= $rl['qui'] ?: '-' ?>
                                                    </td>
                                                    <td
                                                        class="<?= $rl['sex'] > 0 ? 'text-danger fw-bold' : 'text-muted opacity-25' ?>">
                                                        <?= $rl['sex'] ?: '-' ?>
                                                    </td>
                                                    <td
                                                        class="<?= $rl['sab'] > 0 ? 'text-danger fw-bold' : 'text-muted opacity-25' ?>">
                                                        <?= $rl['sab'] ?: '-' ?>
                                                    </td>
                                                    <td class="pe-4 bg-light fw-bold fs-5 text-info border-start">
                                                        <?= $rl['total'] ?>h
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">Nenhum laboratório ocupado.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>