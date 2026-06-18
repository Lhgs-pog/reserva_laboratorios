<?php

/** Modal reutilizável — atender / editar chamado SOS */

if (!function_exists('sos_render_modal_atendimento')) {

    function sos_render_modal_atendimento(): void {

        $statusOpcoes = sos_status_opcoes();

        ?>

        <div class="modal fade" id="modalAtenderChamado" tabindex="-1" aria-labelledby="modalAtenderChamadoLabel" aria-hidden="true">

            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-sos-atendimento">

                <div class="modal-content">

                    <div class="modal-header border-0 pb-0 flex-shrink-0">

                        <div>

                            <h5 class="modal-title fw-bold" id="modalAtenderChamadoLabel">

                                <i class="bi bi-headset text-primary me-2"></i>Atender chamado

                            </h5>

                            <p class="text-muted small mb-0" id="sosModalResumo"></p>

                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>

                    </div>

                    <form method="POST" action="painel_suporte.php?aba=sessao-sos-ativos" id="formAtenderChamado" class="modal-sos-atendimento-form">

                        <input type="hidden" name="atualizar_chamado_sos" value="1">

                        <input type="hidden" name="id_chamado" id="sosModalId" value="">

                        <div class="modal-body pt-2">

                            <div class="alert alert-light border small mb-3" id="sosModalProblema"></div>



                            <div class="mb-3" id="sosModalHistoricoWrap">

                                <label class="form-label small fw-bold mb-2">

                                    <i class="bi bi-clock-history me-1"></i>Histórico de atendimento

                                </label>

                                <div id="sosModalHistorico" class="sos-historico-timeline border rounded bg-light p-2">

                                    <p class="text-muted small mb-0 fst-italic">Nenhuma atualização registrada ainda.</p>

                                </div>

                            </div>



                            <hr class="text-muted opacity-25">



                            <p class="small text-muted mb-3">Registre abaixo uma <strong>nova</strong> observação ou resposta. Cada salvamento entra no histórico acima.</p>



                            <div class="row g-3">

                                <div class="col-md-6">

                                    <label class="form-label small fw-bold">Status do chamado</label>

                                    <select name="status" id="sosModalStatus" class="form-select" required>

                                        <?php foreach ($statusOpcoes as $valor => $rotulo): ?>

                                            <option value="<?= htmlspecialchars($valor) ?>"><?= htmlspecialchars($rotulo) ?></option>

                                        <?php endforeach; ?>

                                    </select>

                                    <div class="form-text">Use <em>Em andamento</em> ao iniciar, <em>Aguardando verificação</em> se precisar que o professor confirme, <em>Resolvido</em> ou <em>Não resolvido</em> ao encerrar.</div>

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label small fw-bold">Professor</label>

                                    <input type="text" class="form-control bg-light" id="sosModalProfessor" readonly>

                                </div>

                                <div class="col-12">

                                    <label class="form-label small fw-bold">Nova observação interna <span class="text-muted fw-normal">(só a equipe TI vê)</span></label>

                                    <textarea name="observacao_interna" id="sosModalObsInterna" class="form-control" rows="2" placeholder="Ex.: troca de cabo HDMI, aguardando peça, escalado para terceirizada..."></textarea>

                                </div>

                                <div class="col-12">

                                    <label class="form-label small fw-bold">Nova resposta ao professor</label>

                                    <textarea name="resposta_professor" id="sosModalResposta" class="form-control" rows="3" placeholder="Explique o que foi feito, pendências ou orientações para o professor..."></textarea>

                                </div>

                                <div class="col-12">

                                    <div class="form-check">

                                        <input class="form-check-input" type="checkbox" name="enviar_email" value="1" id="sosModalEnviarEmail" checked>

                                        <label class="form-check-label small" for="sosModalEnviarEmail">

                                            Enviar e-mail ao professor com esta resposta e o status atual

                                        </label>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="modal-footer border-top bg-body sticky-bottom">

                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>

                            <button type="submit" class="btn btn-primary fw-bold">

                                <i class="bi bi-save me-1"></i> Salvar atualização

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

        <?php

    }

}

