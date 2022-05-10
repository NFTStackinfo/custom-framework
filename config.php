<?php

$KERNEL_CONFIG = [
    // Autoloader instructions
    'autoloader' => [
        'prefixes' => [
            // Core prefixes
            'Db' => '/Core/Db',
            'Engine' => '/Core/Engine',
            'Core' => '/Core',

            // App prefixes
            'Migrations' => '/Database/Migrations',
            'Models' => '/Models',
            'Modules' => '/Modules',
            'Api' => '/Api',
            'Middlewares' => '/Middlewares',
            'Serializers' => '/Serializers',
        ],
    ],

    // Cross-Origin Resource Sharing instructions
    'cors' => [
        'allow' => [
            'origin' => ['*'],
            'method' => [
                'GET',
                'POST',
                'PUT',
                'DELETE',
            ],
        ],
    ],

    // App configs
    'model' => [
        // hard/soft
        'delete' => 'soft',
    ],

    // Debugger
    'debug' => getenv('APP_DEBUG') == 'true' ? true : false,

    // DB Connection Credentials
    'db' => [
        'host' => getenv('DB_HOST'),
        'name' => getenv('DB_NAME'),
        'user' => [
            'name' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
        ],
    ],
];
