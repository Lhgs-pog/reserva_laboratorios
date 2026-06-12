<div id="sessao-historico-geral" class="content-section">
            <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--info);">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-info me-2"></i> Histórico
                        Geral de Solicitações</h5>
                    <p class="text-muted small mb-0 mt-1">Acompanhe quem está solicitando os laboratórios e o status de
                        cada pedido.</p>
                </div>
                <div class="card-body p-0" id="container-tabela-historico-geral">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
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
                                        <tr>
                                            <td class="ps-4">
                                                <strong><?= date('d/m/Y', strtotime($h['data_reserva'])) ?></strong>
                                            </td>
                                            <td class="fw-bold text-primary"><i
                                                    class="bi bi-person-badge me-2"></i><?= htmlspecialchars($h['professor']) ?>
                                            </td>
                                            <td><span
                                                    class="badge bg-secondary"><?= htmlspecialchars($h['laboratorio']) ?></span><br><small
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
                                            <td class="pe-4 text-end">
                                                <?php if ($h['status'] == 'pendente'): ?>
                                                    <form method="POST" action="painel_coordenador.php" class="d-inline form-acao-reserva">
                                                        <input type="hidden" name="id_agendamento" value="<?= $h['id'] ?>">
                                                        <button type="button" name="acao_reserva" value="aprovar" data-acao="aprovar" class="btn btn-sm btn-success rounded-circle btn-acao-reserva" title="Aprovar"><i class="bi bi-check-lg"></i></button>
                                                    </form>
                                                    <form method="POST" action="painel_coordenador.php" class="d-inline ms-1 form-acao-reserva">
                                                        <input type="hidden" name="id_agendamento" value="<?= $h['id'] ?>">
                                                        <button type="button" name="acao_reserva" value="rejeitar" data-acao="rejeitar" class="btn btn-sm btn-danger rounded-circle btn-acao-reserva" title="Rejeitar"><i class="bi bi-x-lg"></i></button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted"><i
                                                class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>Nenhum registro de
                                            solicitação encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>