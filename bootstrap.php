<?php
/**
 * Bootstrap compartilhado — autoload App\ e despacho MVC.
 */
require_once __DIR__ . '/app/Config/session_bootstrap.php';
labhub_session_start();

if (!function_exists('labhub_register_autoload')) {
    function labhub_register_autoload(): void
    {
        static $registered = false;
        if ($registered) {
            return;
        }
        $registered = true;

        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
        }

        spl_autoload_register(function ($class) {
            $prefix = 'App\\';
            $base_dir = __DIR__ . '/app/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }
            $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
            if (file_exists($file)) {
                require $file;
            }
        });
    }
}

function labhub_dispatch(): mixed
{
    labhub_register_autoload();
    return require __DIR__ . '/app/router.php';
}
