<?php

$controllers = [
    'AdminController' => 'Admin\Controller\AdminController',
    'AppController' => 'App\Controller\AppController',
    'AuthController' => 'Security\Controller\AuthController',
    'UserController' => 'App\Controller\UserController'
];

foreach ($controllers as $key => $class) {
    $container[$key] = function ($container) use ($class) {
        return new $class($container);
    };
}
