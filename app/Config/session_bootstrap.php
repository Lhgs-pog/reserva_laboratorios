<?php

declare(strict_types=1);

/**
 * Sessões persistentes (PostgreSQL) + "Lembrar-me" — sobrevivem a deploy no Fly.io.
 */

use App\Config\Database;

final class LabhubDbSessionHandler implements \SessionHandlerInterface
{
    private PDO $pdo;
    private bool $isPgsql;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->isPgsql = app_db_driver() === 'pgsql';
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        if ($this->isPgsql) {
            $this->pdo->exec(
                'CREATE TABLE IF NOT EXISTS php_sessions (
                    id VARCHAR(128) PRIMARY KEY,
                    data TEXT NOT NULL DEFAULT \'\',
                    last_access TIMESTAMPTZ NOT NULL DEFAULT NOW()
                )'
            );
            $this->pdo->exec(
                'CREATE TABLE IF NOT EXISTS auth_remember_tokens (
                    id SERIAL PRIMARY KEY,
                    id_usuario INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                    token_hash VARCHAR(64) NOT NULL UNIQUE,
                    expires_at TIMESTAMPTZ NOT NULL,
                    criado_em TIMESTAMPTZ NOT NULL DEFAULT NOW()
                )'
            );
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_remember_usuario ON auth_remember_tokens(id_usuario)');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_remember_expires ON auth_remember_tokens(expires_at)');
            return;
        }

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS php_sessions (
                id VARCHAR(128) NOT NULL PRIMARY KEY,
                data TEXT NOT NULL,
                last_access DATETIME NOT NULL,
                INDEX idx_php_sessions_last (last_access)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS auth_remember_tokens (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT UNSIGNED NOT NULL,
                token_hash VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_remember_hash (token_hash),
                KEY idx_remember_usuario (id_usuario),
                KEY idx_remember_expires (expires_at),
                CONSTRAINT fk_remember_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function open($path, $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string|false
    {
        $stmt = $this->pdo->prepare('SELECT data FROM php_sessions WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (string) $row['data'] : '';
    }

    public function write($id, $data): bool
    {
        try {
            if ($this->isPgsql) {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO php_sessions (id, data, last_access) VALUES (?, ?, NOW())
                     ON CONFLICT (id) DO UPDATE SET data = EXCLUDED.data, last_access = NOW()'
                );
            } else {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO php_sessions (id, data, last_access) VALUES (?, ?, NOW())
                     ON DUPLICATE KEY UPDATE data = VALUES(data), last_access = NOW()'
                );
            }
            $ok = $stmt->execute([$id, $data]);
            if (!$ok) {
                error_log('[labhub_session] write falhou: ' . implode(' ', $stmt->errorInfo() ?: []));
            }
            return $ok;
        } catch (\Throwable $e) {
            error_log('[labhub_session] write exception: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM php_sessions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function gc($maxlifetime): int|false
    {
        try {
            if ($this->isPgsql) {
                $stmt = $this->pdo->prepare(
                    'DELETE FROM php_sessions WHERE last_access < NOW() - (? || \' seconds\')::interval'
                );
                $stmt->execute([(string) (int) $maxlifetime]);
            } else {
                $cutoff = date('Y-m-d H:i:s', time() - (int) $maxlifetime);
                $stmt = $this->pdo->prepare('DELETE FROM php_sessions WHERE last_access < ?');
                $stmt->execute([$cutoff]);
            }
            return $stmt->rowCount();
        } catch (\Throwable $e) {
            error_log('[labhub_session] gc exception: ' . $e->getMessage());
            return false;
        }
    }
}

function labhub_bootstrap_autoload(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $root = dirname(__DIR__, 2);
    if (file_exists($root . '/vendor/autoload.php')) {
        require_once $root . '/vendor/autoload.php';
    }

    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
            return;
        }
        $file = dirname(__DIR__) . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}

function labhub_session_driver(): string
{
    require_once __DIR__ . '/env.php';
    app_load_env(dirname(__DIR__, 2));
    return strtolower(trim((string) app_env('SESSION_DRIVER', 'db')));
}

function labhub_is_safe_return_url(string $url): bool
{
    $url = trim($url);
    if ($url === '' || str_contains($url, '://') || str_starts_with($url, '//')) {
        return false;
    }
    if (!str_starts_with($url, '/')) {
        return false;
    }
    // Delimitador ~ evita conflito com / ? # na URL
    return (bool) preg_match('~^/[a-zA-Z0-9\-._/?&=%#]*$~', $url);
}

function labhub_redirect_login(?string $reason = null): void
{
    require_once __DIR__ . '/http_helpers.php';

    if (labhub_wants_json()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'           => false,
            'error'        => 'sessao_expirada',
            'qtd'          => 0,
            'items'        => [],
            'qtd_suporte'  => 0,
            'html_suporte' => '',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $return = $_SERVER['REQUEST_URI'] ?? '';
    $params = [];
    if ($return !== '' && labhub_is_safe_return_url($return) && !str_contains($return, 'index.php')) {
        $params['redirect'] = $return;
    }
    $qs = $params !== [] ? '?' . http_build_query($params) : '';
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    header('Location: index.php' . $qs);
    exit;
}

function labhub_restore_remember_me(PDO $pdo): void
{
    if (!empty($_SESSION['usuario_id'])) {
        return;
    }
    $cookie = $_COOKIE['labhub_remember'] ?? '';
    if (!is_string($cookie) || strlen($cookie) < 32) {
        return;
    }

    $hash = hash('sha256', $cookie);
    $isPgsql = app_db_driver() === 'pgsql';
    $sql = $isPgsql
        ? 'SELECT u.id, u.nome, u.email, u.perfil, u.foto_perfil
           FROM auth_remember_tokens t
           JOIN usuarios u ON u.id = t.id_usuario
           WHERE t.token_hash = ? AND t.expires_at > NOW()
           LIMIT 1'
        : 'SELECT u.id, u.nome, u.email, u.perfil, u.foto_perfil
           FROM auth_remember_tokens t
           JOIN usuarios u ON u.id = t.id_usuario
           WHERE t.token_hash = ? AND t.expires_at > NOW()
           LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hash]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$usuario) {
        labhub_clear_remember_cookie();
        return;
    }

    $_SESSION['usuario_id'] = (int) $usuario['id'];
    $_SESSION['nome'] = $usuario['nome'];
    $_SESSION['email'] = $usuario['email'];
    $_SESSION['perfil'] = $usuario['perfil'];
    $_SESSION['foto_perfil'] = $usuario['foto_perfil'] ?? null;
}

function labhub_issue_remember_token(PDO $pdo, int $idUsuario): void
{
    labhub_revoke_remember_tokens($pdo, $idUsuario);

    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $days = max(1, (int) app_env('REMEMBER_ME_DAYS', '30'));

    if (app_db_driver() === 'pgsql') {
        $stmt = $pdo->prepare(
            'INSERT INTO auth_remember_tokens (id_usuario, token_hash, expires_at)
             VALUES (?, ?, NOW() + (? || \' days\')::interval)'
        );
        $stmt->execute([$idUsuario, $hash, (string) $days]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO auth_remember_tokens (id_usuario, token_hash, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? DAY))'
        );
        $stmt->execute([$idUsuario, $hash, $days]);
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    setcookie('labhub_remember', $token, [
        'expires'  => time() + ($days * 86400),
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function labhub_revoke_remember_tokens(PDO $pdo, int $idUsuario): void
{
    $stmt = $pdo->prepare('DELETE FROM auth_remember_tokens WHERE id_usuario = ?');
    $stmt->execute([$idUsuario]);
}

function labhub_clear_remember_cookie(): void
{
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    setcookie('labhub_remember', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function labhub_login_destination(string $perfil, ?string $returnUrl = null): string
{
    if ($returnUrl !== null && labhub_is_safe_return_url($returnUrl)) {
        return $returnUrl;
    }

    $destinos = [
        'coordenador' => 'painel_coordenador.php',
        'suporte'     => 'painel_suporte.php',
        'professor'   => 'painel_professor.php',
    ];

    return $destinos[$perfil] ?? 'index.php';
}

function labhub_session_pdo(): ?\PDO
{
    static $pdo = null;
    if ($pdo instanceof \PDO) {
        return $pdo;
    }

    labhub_bootstrap_autoload();
    require_once __DIR__ . '/Database.php';
    require_once __DIR__ . '/db_dsn.php';
    require_once __DIR__ . '/sql_helpers.php';

    try {
        $pdo = Database::getInstance()->getPDO();
        return $pdo;
    } catch (\Throwable $e) {
        error_log('[labhub_session_pdo] ' . $e->getMessage());
        return null;
    }
}

function labhub_session_start(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    require_once __DIR__ . '/env.php';
    app_load_env(dirname(__DIR__, 2));
    labhub_bootstrap_autoload();

    if (app_env('APP_ENV', 'production') === 'production') {
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
    }

    $lifetime = max(3600, (int) app_env('SESSION_LIFETIME', '28800'));
    ini_set('session.gc_maxlifetime', (string) $lifetime);

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (labhub_session_driver() === 'db') {
        $pdo = labhub_session_pdo();
        if ($pdo instanceof \PDO) {
            try {
                session_set_save_handler(new LabhubDbSessionHandler($pdo), true);
            } catch (\Throwable $e) {
                error_log('[labhub_session_start] DB handler fallback: ' . $e->getMessage());
            }
        } else {
            error_log('[labhub_session_start] PDO indisponível — sessão em arquivo (handler db ignorado)');
        }
    }

    session_start();

    $pdo = labhub_session_pdo();
    if ($pdo instanceof \PDO && empty($_SESSION['usuario_id'])) {
        try {
            labhub_restore_remember_me($pdo);
        } catch (\Throwable $e) {
            error_log('[labhub_session_start] remember-me: ' . $e->getMessage());
        }
    }
}
