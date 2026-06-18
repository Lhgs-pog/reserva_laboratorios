<?php

/**
 * Carrega variáveis de ambiente de um arquivo .env na raiz do projeto.
 * Em produção (Cloud Run / Firebase), use variáveis injetadas pelo provedor.
 */
function app_load_env(string $rootDir): void
{
    $envFile = $rootDir . '/.env';
    if (!is_readable($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if ($name === '' || getenv($name) !== false) {
            continue;
        }

        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

function app_env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}

/** Valida formato de e-mail (qualquer domínio: Gmail, Outlook, etc.) */
function app_email_institucional_valido(string $email): bool
{
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
}

/** Painel sem relatórios BI pesados — acelera abertura após login */
function app_is_fast_panel(): bool
{
    $v = app_env('APP_FAST_PANEL', '1');
    return $v === '1' || strtolower($v) === 'true' || strtolower($v) === 'yes';
}

/** URL pública base (Fly.io, local, etc.) — usada em og:image para WhatsApp. */
function app_base_url(): string
{
    $fromEnv = trim((string) app_env('APP_URL', ''));
    if ($fromEnv !== '') {
        return rtrim($fromEnv, '/');
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

/** Intervalo de datas SQL (MySQL / PostgreSQL). */
function app_sql_date_between(string $column, int $daysBefore, int $daysAfter): string
{
    if (app_db_driver() === 'pgsql') {
        return "{$column} BETWEEN CURRENT_DATE - INTERVAL '{$daysBefore} days' AND CURRENT_DATE + INTERVAL '{$daysAfter} days'";
    }
    return "{$column} BETWEEN DATE_SUB(CURDATE(), INTERVAL {$daysBefore} DAY) AND DATE_ADD(CURDATE(), INTERVAL {$daysAfter} DAY)";
}

function app_boot_database(): void
{
    $pdo = \App\Config\Database::getInstance()->getPDO();
    require_once __DIR__ . '/sos_schema.php';
    require_once __DIR__ . '/sos_helpers.php';
    app_ensure_sos_schema($pdo);
}
