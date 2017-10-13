<?php
use App\Resources\Middleware\CountryMiddleware;

$app->get('/', 'app.controller:redirectHome')->setName('wrong-entry');

$app->group('/{country:[a-z]{2}}', function () {
    $this->get('', 'app.controller:home')->setName('home');
    $this->get('/users', 'app.controller:getUsers')->setName('users');
})->add(new CountryMiddleware($container));




