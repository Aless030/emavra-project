<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost:8001',
        'http://127.0.0.1:8001',
        'http://192.168.104.201:8001',
        'http://192.168.104.201:8002',
        'http://emavraforestaldev.cochabamba.bo:8001',      // ⬅️ AGREGAR
        'http://emavraforestalapidev.cochabamba.bo:8002',  // ⬅️ AGREGAR
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'Origin',
        'Access-Control-Request-Method',
        'Access-Control-Request-Headers',
    ],
    'exposed_headers' => [
        'Cache-Control',
        'Content-Language',
        'Content-Type',
        'Expires',
        'Last-Modified',
        'Pragma',
    ],
    'max_age' => 3600,
    'supports_credentials' => true,
];