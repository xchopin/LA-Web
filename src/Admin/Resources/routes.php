<?php

use App\Resources\Middleware\CountryMiddleware;
use \Security\Resources\Middleware\AdminMiddleware;

$app->group('/{country:[a-z]{2}}', function () use ($container) {

    $this->group('/tools', function () use ($container) {
        $this->map(['GET', 'POST'], '/find-student', 'AdminController:findStudent')->setName('find-student');
    })->add(new AdminMiddleware($container));

})->add(new CountryMiddleware($container));