<?php

declare(strict_types=1);

require_once __DIR__ . '/http_helpers.php';

function labhub_csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function labhub_csrf_verify(): bool
{
    $expected = $_SESSION['csrf_token'] ?? '';
    if (!is_string($expected) || $expected === '') {
        return false;
    }

    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || $token === '') {
        return false;
    }

    return hash_equals($expected, $token);
}

function labhub_csrf_fail(): never
{
    if (labhub_wants_json()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'success' => false, 'error' => 'csrf_invalido'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(403);
    exit('Requisição inválida. Recarregue a página e tente novamente.');
}

function labhub_csrf_route_exempt(?array $route): bool
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return true;
    }
    if ($route === null) {
        return false;
    }

    [$controller, $action] = $route;
    return $controller === 'AuthController'
        && in_array($action, ['login', 'esqueciSenha', 'redefinirSenha'], true);
}

function labhub_csrf_require_post(?array $route = null): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }
    if (labhub_csrf_route_exempt($route)) {
        return;
    }
    if (!labhub_csrf_verify()) {
        labhub_csrf_fail();
    }
}
