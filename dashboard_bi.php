<?php
/**
 * dashboard_bi.php
 *
 * Arquivo de compatibilidade criado conforme relatório técnico de auditoria.
 * O login via Google redirecionava coordenadores para este arquivo (inexistente),
 * causando HTTP 404. Este arquivo verifica a sessão e redireciona corretamente.
 */

require_once __DIR__ . '/app/Config/session_bootstrap.php';
labhub_session_start();

// Redireciona para o painel correto conforme perfil
if (!isset($_SESSION['usuario_id'])) {
    labhub_redirect_login('expired');
}

$destinos = [
    'coordenador' => 'painel_coordenador.php',
    'suporte'     => 'painel_suporte.php',
    'professor'   => 'painel_professor.php',
];

$url = $destinos[$_SESSION['perfil']] ?? 'index.php';
header("Location: " . $url);
exit;
?>
