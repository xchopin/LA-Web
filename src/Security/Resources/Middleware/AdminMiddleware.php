<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Security\Resources\Middleware;

use App\Resources\Middleware\Middleware;
use Slim\Http\Request;
use Slim\Http\Response;

class AdminMiddleware extends Middleware
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
       if (isset($_SESSION['phpCAS']['user']))  {
            if (in_array($_SESSION['phpCAS']['user'], $this->container['parameters']['administrators']))
                return $next($request, $response);
       }

       $this->flash->addMessage('error', '<i class="minus circle icon"></i> You are not allowed to access to this content.');
       return $response->withRedirect($this->router->pathFor('wrong-entry'));
    }
}
