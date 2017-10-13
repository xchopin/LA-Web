<?php
use App\Resources\Middleware\CountryMiddleware;

$app->get('/', 'AppController:redirectHome')->setName('wrong-entry');

$app->group('/{country:[a-z]{2}}', function () {

    $this->get('', 'AppController:home')->setName('home');
    $this->get('/users', 'AppController:getUsers')->setName('users');

})->add(new CountryMiddleware($container));




