<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Resources\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

class AuthMiddleware extends Middleware
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        return isset($_SESSION['phpCAS']['user']) ? $next($request, $response) : $response->withRedirect($this->router->pathFor('wrong-entry'));
    }
}
