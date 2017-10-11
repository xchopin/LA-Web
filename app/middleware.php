<?php

$app->add(new App\Resources\Middleware\CsrfMiddleware($container));
$app->add(new Slim\Csrf\Guard());
