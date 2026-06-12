<?php
/**
 * Sino + popup de notificações na navbar.
 * Variáveis opcionais: $notif_qtd (int), $notif_extra_badges (array de IDs)
 */
$notif_qtd = isset($notif_qtd) ? (int) $notif_qtd : 0;
$notif_extra_badges = $notif_extra_badges ?? [];
?>
<div class="notif-nav-wrap me-4" id="notifNavWrap">
    <div class="position-relative top-icon-btn" id="navBell" title="Notificações" role="button"
        aria-expanded="false" aria-controls="notificacoes-popup" tabindex="0">
        <i class="bi bi-bell"></i>
        <span id="badge-nav-bell"
            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light text-white <?= $notif_qtd > 0 ? '' : 'd-none' ?>"
            style="font-size: 0.65rem; padding: 0.25em 0.4em; pointer-events: none;"><?= $notif_qtd ?></span>
    </div>
    <div id="notificacoes-popup" class="notif-popup d-none" role="dialog" aria-label="Notificações">
        <div class="notif-popup-header">
            <span><i class="bi bi-bell-fill me-2"></i>Notificações</span>
            <button type="button" class="btn-close btn-close-sm" id="notifFechar" aria-label="Fechar"></button>
        </div>
        <div id="notificacoes-lista" class="notif-popup-body">
            <div class="notif-empty text-muted small py-4 text-center">Carregando...</div>
        </div>
        <div class="notif-popup-footer">
            <button type="button" id="notifVerTodas">Ver todas</button>
        </div>
    </div>
</div>
