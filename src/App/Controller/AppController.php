<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
Use App\Model\User;

class AppController extends Controller
{
    public function home(Request $request, Response $response)
    {
        return $this->view->render($response, 'App/home.twig');
    }

    public function getUsers(Request $request, Response $response)
    {
        return $this->redirect($request, $response, 'home');
        return $this->view->render($response, 'App/users.twig', ['users' => User::limit(50)->get()]);
    }
}
