<?php

namespace App\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
Use App\Model\User;

class AppController extends Controller
{
    public function home(Request $request, Response $response)
    {
        $users = User::limit(100)->get();
        return $this->view->render($response, 'App/home.twig', ['users' => $users]);
    }
}
