<?php
/**
 * Laravel Configuration - Application Settings
 */

return [
    'name' => 'LabHub UNICEPLAC - Laravel',
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost/labhubuniceplac-main/laravel-app/public'),
    
    'timezone' => 'America/Sao_Paulo',
    'locale' => 'pt_BR',
    
    'key' => env('APP_KEY', 'SomeRandomKeyForEncryption'),
    'cipher' => 'AES-256-CBC',
];
?>
