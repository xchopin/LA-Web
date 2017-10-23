<?php

use App\Resources\Middleware\CountryMiddleware;
use \Security\Resources\Middleware\PersistentCSRFToken;

$app->group('/{country:[a-z]{2}}', function () use($container) {

    $this->group('/tools', function () use($container) {
        $this->map(['GET', 'POST'], '/find-student', 'AdminController:findStudent')->setName('find-student');
    });

})->add(new CountryMiddleware($container));