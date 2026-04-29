<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configurado para permitir requests desde:
    |   - Ionic dev server (http://localhost:8100)
    |   - Capacitor en simulador iOS (capacitor://localhost)
    |   - Capacitor en Android (http://localhost)
    |   - Dominio de produccion (configurar en .env via APP_URL)
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('APP_URL', 'http://localhost:8000'),
        'http://localhost',
        'http://localhost:8100',
        'http://localhost:4200',
        'capacitor://localhost',
        'ionic://localhost',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
