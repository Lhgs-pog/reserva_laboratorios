<div id="sessao-historico-geral" class="content-section">
            <?php if (!empty($lista_espera_geral)): ?>
            <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--warning);">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-hourglass-bottom text-warning me-2"></i> Fila de espera — laboratórios lotados</h5>
                    <p class="text-muted small mb-0 mt-1">Professores aguardando vaga. Quando alguém desistir ou uma reserva for rejeitada, <strong>aloque</strong> o primeiro da fila em um lab livre.</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3">Posição</th>
                                    <th>Professor</th>
                                    <th>Data / Horário</th>
                                    <th>Disciplina</th>
                                    <th class="pe-4 text-end">Alocar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista_espera_geral as $le): ?>
                                    <tr>
                                        <td class="ps-4"><span class="badge bg-info rounded-pill px-3"><?= (int) $le['posicao'] ?>º</span></td>
                                        <td class="fw-bold text-primary"><i class="bi bi-person me-1"></i><?= htmlspecialchars($le['professor']) ?></td>
                                        <td><strong><?= date('d/m/Y', strtotime($le['data_reserva'])) ?></strong><br><small class="text-muted"><?= htmlspecialchars($le['turno']) ?> · <?= htmlspecialchars($le['periodo']) ?></small></td>
                                        <td><?= htmlspecialchars($le['disciplina']) ?></td>
                                        <td class="pe-4 text-end">
                                            <?php if (!empty($le['labs_livres'])): ?>
                                                <form method="POST" action="painel_coordenador.php" class="d-inline-flex align-items-center gap-2 justify-content-end flex-wrap">
                                                    <input type="hidden" name="alocar_lista_espera" value="1">
                                                    <input type="hidden" name="id_lista_espera" value="<?= (int) $le['id'] ?>">
                                                    <select name="id_laboratorio" class="form-select form-select-sm" style="max-width: 220px;" required>
                                                        <option value="">Lab livre…</option>
                                                        <?php foreach ($le['labs_livres'] as $labLivre): ?>
                                                            <option value="<?= (int) $labLivre['id'] ?>"><?= htmlspecialchars($labLivre['nome']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-success fw-bold"><i class="bi bi-person-check me-1"></i>Alocar</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small"><i class="bi bi-lock me-1"></i>Slot lotado — rejeite/cancele uma reserva para liberar vaga</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--info);">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-info me-2"></i> Histórico
                        Geral de Solicitações</h5>
                    <p class="text-muted small mb-0 mt-1">Acompanhe quem está solicitando os laboratórios e o status de
                        cada pedido. Pendentes e aprovadas podem ser <strong>realocadas</strong> para outro lab livre no mesmo horário.</p>
                </div>
                <div class="card-body p-0" id="container-tabela-historico-geral">
                    <div class="table-responsive lh-table-scroll">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3">Data da Reserva</th>
                                    <th>Professor Solicitante</th>
                                    <th>Laboratório / Disciplina</th>
                                    <th>Turno / Horário</th>
                                    <th>Status</th>
                                    <th class="pe-4 text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($historico_completo) > 0): ?>
                                    <?php foreach ($historico_completo as $h): ?>
                                        <tr data-reserva-id="<?= (int) $h['id'] ?>"
                                            data-id-laboratorio="<?= (int) $h['id_laboratorio'] ?>"
                                            data-professor="<?= htmlspecialchars($h['professor'], ENT_QUOTES) ?>"
                                            data-disciplina="<?= htmlspecialchars($h['disciplina'], ENT_QUOTES) ?>"
                                            data-laboratorio="<?= htmlspecialchars($h['laboratorio'], ENT_QUOTES) ?>"
                                            data-data="<?= date('d/m/Y', strtotime($h['data_reserva'])) ?>"
                                            data-turno="<?= htmlspecialchars($h['turno'], ENT_QUOTES) ?>"
                                            data-periodo="<?= htmlspecialchars($h['periodo'], ENT_QUOTES) ?>">
                                            <td class="ps-4">
                                                <strong><?= date('d/m/Y', strtotime($h['data_reserva'])) ?></strong>
                                            </td>
                                            <td class="fw-bold text-primary"><i
                                                    class="bi bi-person-badge me-2"></i><?= htmlspecialchars($h['professor']) ?>
                                            </td>
                                            <td class="td-lab-disc"><span
                                                    class="badge bg-secondary badge-lab-nome"><?= htmlspecialchars($h['laboratorio']) ?></span><br><small
                                                    class="text-muted"><?= htmlspecialchars($h['disciplina']) ?></small></td>
                                            <td><?= htmlspecialchars($h['turno']) ?> <br><small
                                                    class="text-muted"><?= htmlspecialchars($h['periodo']) ?></small></td>
                                            <td>
                                                <?php if ($h['status'] == 'aprovado')
                                                    echo '<span class="badge bg-success rounded-pill px-3">Aprovado</span>';
                                                elseif ($h['status'] == 'pendente')
                                                    echo '<span class="badge bg-warning text-dark rounded-pill px-3">Pendente</span>';
                                                else
                                                    echo '<span class="badge bg-danger rounded-pill px-3">Rejeitado</span>'; ?>
                                            </td>
                                            <td class="pe-4 text-end td-acoes-reserva">
                                                <?php if ($h['status'] == 'pendente'): ?>
                                                    <form method="POST" action="painel_coordenador.php" class="d-inline form-acao-reserva">
                                                        <input type="hidden" name="id_agendamento" value="<?= $h['id'] ?>">
                                                        <button type="button" name="acao_reserva" value="aprovar" data-acao="aprovar" class="btn btn-sm btn-success rounded-circle btn-acao-reserva" title="Aprovar"><i class="bi bi-check-lg"></i></button>
                                                    </form>
                                                    <form method="POST" action="painel_coordenador.php" class="d-inline ms-1 form-acao-reserva">
                                                        <input type="hidden" name="id_agendamento" value="<?= $h['id'] ?>">
                                                        <button type="button" name="acao_reserva" value="rejeitar" data-acao="rejeitar" class="btn btn-sm btn-danger rounded-circle btn-acao-reserva" title="Rejeitar"><i class="bi bi-x-lg"></i></button>
                                                    </form>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary btn-realocar-reserva ms-1"
                                                        title="Realocar para outro laboratório"
                                                        data-reserva-id="<?= (int) $h['id'] ?>">
                                                        <i class="bi bi-arrow-left-right me-1"></i> Realocar
                                                    </button>
                                                <?php elseif ($h['status'] == 'aprovado'): ?>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary btn-realocar-reserva"
                                                        title="Realocar para outro laboratório"
                                                        data-reserva-id="<?= (int) $h['id'] ?>">
                                                        <i class="bi bi-arrow-left-right me-1"></i> Realocar
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted"><i
                                                class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>Nenhum registro de
                                            solicitação encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalRealocarReserva" tabindex="-1" aria-labelledby="modalRealocarLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h5 class="modal-title fw-bold" id="modalRealocarLabel">
                                    <i class="bi bi-arrow-left-right text-primary me-2"></i>Realocar reserva
                                </h5>
                                <p class="text-muted small mb-0" id="realocarResumo"></p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body pt-3">
                            <div id="realocarAlerta" class="alert d-none mb-3" role="alert"></div>
                            <div id="realocarCarregando" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div> Verificando disponibilidade...
                            </div>
                            <div id="realocarListaLabs" class="d-none">
                                <p class="small text-muted mb-2">Selecione um laboratório <span class="text-success fw-semibold">livre</span> no mesmo dia e horário:</p>
                                <div class="list-group" id="realocarLabsContainer"></div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary fw-bold" id="btnConfirmarRealocacao" disabled>
                                Confirmar realocação
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
