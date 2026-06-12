<?php

function app_db_driver(): string
{
    return app_env('DB_CONNECTION', 'mysql') ?? 'mysql';
}

function app_build_pdo_dsn(
    string $driver,
    string $host,
    string $port,
    string $dbname,
    ?string $cloudSqlInstance = null
): string {
    if ($driver === 'pgsql') {
        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $dbname);
        $sslmode = app_env('DB_SSLMODE', 'require');
        if ($sslmode !== '' && $sslmode !== 'disable') {
            $dsn .= ';sslmode=' . $sslmode;
        }
        return $dsn;
    }

    if ($cloudSqlInstance !== null && $cloudSqlInstance !== '') {
        return sprintf(
            'mysql:unix_socket=/cloudsql/%s;dbname=%s;charset=utf8mb4',
            $cloudSqlInstance,
            $dbname
        );
    }

    return sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        $dbname
    );
}

function app_apply_db_timezone(PDO $pdo, string $driver): void
{
    if ($driver === 'pgsql') {
        $pdo->exec("SET TIME ZONE 'America/Sao_Paulo'");
        return;
    }
    $pdo->exec("SET time_zone = '-03:00'");
}
