<?php

return [
    'env' => 'dev',
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => false,
        'assets' => [
            'base_path' => '../../'
        ],
        'view' => [
            'templates_path' => [
                dirname(__DIR__) . '/../src/App/View',
                dirname(__DIR__) . '/../src/Admin/View',
            ],
            'twig' => [
                'debug' => true,
                'auto_reload' => true
            ]
        ],
        'monolog' => [
            'name' => 'app',
            'path' => dirname(__DIR__) . '/../var/logs/dev.log'
        ]
    ]
];
