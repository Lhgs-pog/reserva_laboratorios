<?php
/**
 * Laravel Configuration
 * Entry point for application initialization
 */

define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/../config/database.php';

// Application class loader
class Application {
    protected $basePath;
    protected $config = [];
    protected $services = [];
    protected $routes = [];

    public function __construct($basePath) {
        $this->basePath = $basePath;
        $this->registerBaseBindings();
        $this->registerConfigItems();
    }

    protected function registerBaseBindings() {
        // Bind self instance
        $this->services['app'] = $this;
    }

    protected function registerConfigItems() {
        // Load configuration files
        $this->config['database'] = require $this->basePath . '/config/database.php';
        $this->config['app'] = require $this->basePath . '/config/app.php';
    }

    public function basePath($path = '') {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public function config($key = null, $default = null) {
        if ($key === null) {
            return $this->config;
        }

        $parts = explode('.', $key);
        $value = $this->config;

        foreach ($parts as $part) {
            if (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } else {
                return $default;
            }
        }

        return $value;
    }

    public function bind($abstract, $concrete) {
        $this->services[$abstract] = $concrete;
    }

    public function make($abstract) {
        if (isset($this->services[$abstract])) {
            $concrete = $this->services[$abstract];
            return is_callable($concrete) ? $concrete($this) : $concrete;
        }
        return null;
    }

    public function registerRoutes() {
        require $this->basePath . '/routes/web.php';
        require $this->basePath . '/routes/api.php';
    }

    public static function getInstance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self(__DIR__ . '/..');
        }
        return $instance;
    }
}

// Create application instance
$app = Application::getInstance();

return $app;
?>
