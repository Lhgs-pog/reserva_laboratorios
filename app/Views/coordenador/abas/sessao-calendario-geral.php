<div id="sessao-calendario-geral" class="content-section">
            <div class="card shadow-sm border-0 mb-4 lh-cal-card">
                <div class="card-header lh-card-header py-3">
                    <h5 class="mb-0 fw-bold lh-title-verde d-flex align-items-center"><i
                            class="bi bi-calendar3 me-3 fs-4"></i> Calendário Consolidado</h5>
                </div>
                <div class="card-body lh-card-body p-3 p-md-4">
                    <?php $calendario_modo = 'coordenador'; require __DIR__ . '/../../partials/calendario-legenda.php'; ?>
                    <div class="lh-calendar-wrap">
                        <div id="calendarioGeral"></div>
                    </div>
                </div>
            </div>
        </div>
