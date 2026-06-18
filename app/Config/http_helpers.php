<?php

declare(strict_types=1);

function labhub_wants_json(): bool
{
    $page = $_GET['page'] ?? basename($_SERVER['PHP_SELF'] ?? '', '.php');
    if (in_array($page, ['check_notificacoes', 'check_sos_status', 'check_sos', 'api_cadastros'], true)) {
        return true;
    }

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (str_contains($accept, 'application/json')) {
        return true;
    }

    $xhr = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    if ($xhr === 'xmlhttprequest') {
        return true;
    }

    return !empty($_POST['ajax']) || !empty($_GET['ajax']);
}

function labhub_log_exception(string $context, Throwable $e): void
{
    error_log("[{$context}] " . $e->getMessage());
}

function labhub_user_error_message(): string
{
    return 'Não foi possível concluir a operação. Tente novamente ou contate o suporte.';
}
