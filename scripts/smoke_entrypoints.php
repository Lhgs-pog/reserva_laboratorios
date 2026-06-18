<?php

declare(strict_types=1);

/**
 * Smoke test local — entry points e funções críticas de sessão.
 * Uso: php scripts/smoke_entrypoints.php
 * Exit 0 = OK, 1 = falha
 */

$root = dirname(__DIR__);
$failures = [];

function fail(string $msg): void
{
    global $failures;
    $failures[] = $msg;
    fwrite(STDERR, "FAIL: {$msg}\n");
}

function ok(string $msg): void
{
    fwrite(STDOUT, "OK: {$msg}\n");
}

require_once $root . '/app/Config/session_bootstrap.php';

$urls = [
    '/painel_professor.php',
    '/painel_professor.php?aba=sessao-chamados-ti',
    '/painel_coordenador.php?q_id=1#sessao-quadro-horario',
    '/painel_suporte.php?aba=sessao-sos-ativos',
    '/index.php',
];

foreach ($urls as $u) {
    if (!labhub_is_safe_return_url($u)) {
        fail("labhub_is_safe_return_url rejeitou URL válida: {$u}");
    }
}

foreach (['http://evil.com/x', '//evil.com', 'javascript:alert(1)'] as $bad) {
    if (labhub_is_safe_return_url($bad)) {
        fail("labhub_is_safe_return_url aceitou URL inválida: {$bad}");
    }
}
ok('labhub_is_safe_return_url');

$files = [
    'index.php',
    'painel_professor.php',
    'painel_suporte.php',
    'painel_coordenador.php',
    'check_sos_status.php',
    'check_notificacoes.php',
    'app/Config/session_bootstrap.php',
    'app/router.php',
    'app/Models/SOS.php',
];

foreach ($files as $rel) {
    $path = $root . '/' . $rel;
    if (!is_file($path)) {
        fail("arquivo ausente: {$rel}");
        continue;
    }
    exec('php -l ' . escapeshellarg($path) . ' 2>&1', $out, $code);
    if ($code !== 0) {
        fail('syntax ' . $rel . ': ' . implode(' ', $out));
    }
}
ok('syntax PHP dos entry points');

// SOS.php deve referenciar helpers globais
$sos = file_get_contents($root . '/app/Models/SOS.php');
if (!str_contains($sos, '\\sos_sql_in_ativos()')) {
    fail('SOS.php sem prefixo global em sos_sql_in_ativos');
}
if (!str_contains($sos, "require_once __DIR__ . '/../Config/sos_helpers.php'")) {
    fail('SOS.php sem require sos_helpers');
}
ok('SOS.php helpers');

if ($failures !== []) {
    fwrite(STDERR, "\n" . count($failures) . " falha(s).\n");
    exit(1);
}

fwrite(STDOUT, "\nTodos os checks passaram.\n");
exit(0);
