<?php
/** @var string $calendario_modo 'professor'|'coordenador' */
$calendario_modo = $calendario_modo ?? 'coordenador';
?>
<p class="text-muted small mb-2 lh-legenda-calendario">
    <span class="lh-badge lh-badge-fixa">Aulas fixas</span>
    <span class="lh-badge lh-badge-avulsa">Reservas avulsas</span>
    <?php if ($calendario_modo === 'professor'): ?>
        <span class="lh-badge lh-badge-pendente">Aguardando aprovação</span>
        <span class="lh-badge lh-badge-rejeitada">Rejeitadas</span>
    <?php endif; ?>
    <span class="lh-badge lh-badge-feriado">Feriados</span>
    <span class="lh-badge lh-badge-facultativo">Pontos facultativos (DF)</span>
</p>
<p class="lh-cal-fonte-feriados mb-3">
    <i class="bi bi-cloud-check me-1"></i>
    Feriados nacionais via BrasilAPI · Facultativos conforme calendário usual do DF/GDF
</p>
