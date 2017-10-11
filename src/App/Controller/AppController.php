<?php

namespace App\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
Use App\Model\User;
use phpCAS;

class AppController extends Controller
{
    public function home(Request $request, Response $response)
    {
        return $this->view->render($response, 'App/home.twig', [
            'user' => isset($_SESSION['phpCAS']['user']) ? $_SESSION['phpCAS']['user'] : null
        ]);
    }

    public function getUsers(Request $request, Response $response)
    {
        var_dump(User::limit(50)->get());

    }
}
