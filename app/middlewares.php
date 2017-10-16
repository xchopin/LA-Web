<?php

use Security\Resources\Middleware\CsrfMiddleware;

$app->add(new CsrfMiddleware($container));

$app->add(new Slim\Csrf\Guard());