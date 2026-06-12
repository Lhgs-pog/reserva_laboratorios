<?php
/**
 * dashboard_bi.php
 *
 * Arquivo de compatibilidade criado conforme relatório técnico de auditoria.
 * O login via Google redirecionava coordenadores para este arquivo (inexistente),
 * causando HTTP 404. Este arquivo verifica a sessão e redireciona corretamente.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redireciona para o painel correto conforme perfil
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
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
