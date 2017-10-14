<?php

$controllers = [
    'AppController' => 'App\Controller\AppController',
    'AuthController' => 'Security\Controller\AuthController'
];

foreach ($controllers as $key => $class) {
    $container[$key] = function ($container) use ($class) {
        return new $class($container);
    };
}
