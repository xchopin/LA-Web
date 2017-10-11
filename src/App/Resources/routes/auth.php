<?php

use App\Resources\Middleware\GuestMiddleware;
use App\Resources\Middleware\AuthMiddleware;

$app->group('', function () {
    $this->get('/login', 'auth.controller:login')->setName('login');
})->add(new GuestMiddleware($container));

$app->group('', function () {
    $this->get('/logout', 'auth.controller:logout')->setName('logout');
})->add(new AuthMiddleware($container));
