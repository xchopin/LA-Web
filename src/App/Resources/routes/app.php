<?php
use App\Resources\Middleware\AuthMiddleware;


$app->get('/', 'app.controller:home')->setName('home');


$app->group('users', function () {
    $this->get('/', 'app.controller:users')->setName('users');
})->add(new AuthMiddleware($container));



