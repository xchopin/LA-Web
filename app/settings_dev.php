<?php

return [
    'env' => 'dev',
    'settings' => [

        'displayErrorDetails' => true,

        'determineRouteBeforeAppMiddleware' => true,


        'assets' => [
            'base_path' => '../'
        ],

        'view' => [
            'templates_path' => dirname(__DIR__) . '/src/App/View',
            'twig' => [
                'debug' => true,
                'auto_reload' => true
            ]
        ],

        'monolog' => [
            'path' => dirname(__DIR__) . '/var/logs/dev.log'
        ]

    ]
];
