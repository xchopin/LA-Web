<?php

namespace App\Resources\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

class GuestMiddleware extends Middleware
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        if (!isset($_SESSION['phpCAS']['user'])) {
          // Editer l'argument pour le pays return $response->withRedirect($this->router->pathFor('home'));
        }
        return $next($request, $response);
    }
}
