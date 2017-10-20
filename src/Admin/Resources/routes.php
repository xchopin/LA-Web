<?php

use App\Resources\Middleware\CountryMiddleware;

$app->group('/{country:[a-z]{2}}', function () {

    $this->group('/tools', function () {
        $this->map(['GET', 'POST'], '/find-student', 'AdminController:findStudent')->setName('find-student');
    });

})->add(new CountryMiddleware($container));