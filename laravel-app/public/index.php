<?php

/**
 * Laravel Entry Point - index.php
 * 
 * Point all traffic to this file.
 */

define('LARAVEL_START', microtime(true));

// Require the bootstrap file
require __DIR__ . '/../bootstrap/app.php';

// Create request and response objects
$app = Application::getInstance();

// Get the request URI and method
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Simple router
$router = new \LaravelApp\Router();
$response = $router->dispatch($uri, $method);

// Send response
if (is_array($response)) {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    echo $response;
}

exit;

/**
 * Simple Router class
 */
class Router {
    protected $routes = [
        'web' => [],
        'api' => [],
    ];

    public function __construct() {
        $this->registerRoutes();
    }

    protected function registerRoutes() {
        // Import web routes
        $this->routes['web'] = [
            '/' => 'AuthController@login',
            '/login' => 'AuthController@login',
            '/cadastro' => 'AuthController@cadastro',
            '/logout' => 'AuthController@logout',
            '/painel/professor' => 'PainelController@professor',
            '/painel/coordenador' => 'PainelController@coordenador',
            '/painel/suporte' => 'PainelController@suporte',
        ];

        $this->routes['api'] = [
            '/api/agendamentos' => 'Api\AgendamentoController@index',
            '/api/sos/pendentes' => 'Api\SOSController@pendentes',
        ];
    }

    public function dispatch($uri, $method) {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = str_replace('/labhubuniceplac-main/laravel-app/public', '', $uri);
        $uri = '/' . trim($uri, '/');

        // Check web routes
        if (isset($this->routes['web'][$uri])) {
            return $this->callController($this->routes['web'][$uri], $method);
        }

        // Check API routes
        if (isset($this->routes['api'][$uri])) {
            return $this->callController($this->routes['api'][$uri], $method);
        }

        return ['error' => 'Route not found', 'uri' => $uri];
    }

    protected function callController($controller, $method) {
        list($class, $action) = explode('@', $controller);
        $controllerClass = "\\LaravelApp\\Http\\Controllers\\$class";

        if (!class_exists($controllerClass)) {
            return ['error' => "Controller not found: $controllerClass"];
        }

        $instance = new $controllerClass();
        if (!method_exists($instance, $action)) {
            return ['error' => "Action not found: $action"];
        }

        return call_user_func([$instance, $action]);
    }
}

namespace LaravelApp;

class Router extends \Router {}
?>
