<?php

use Security\Resources\Middleware\GuestMiddleware;
use Security\Resources\Middleware\AuthMiddleware;


$app->group('', function ()  {
    $this->get('/login', 'AuthController:login')->setName('login');
})->add(new GuestMiddleware($container));

$app->group('', function () {
        $this->get('/logout', 'AuthController:logout')->setName('logout');
})->add(new AuthMiddleware($container));

