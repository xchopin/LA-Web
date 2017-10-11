<?php

namespace App\Controller;

use Respect\Validation\Validator as V;
use Slim\Http\Request;
use Slim\Http\Response;
use phpCAS;

class AuthController extends Controller
{

    public function login(Request $request, Response $response)
    {
        phpCAS::client(CAS_VERSION_2_0,'auth.univ-lorraine.fr',443,'');
        phpCAS::setNoCasServerValidation();
        phpCAS::forceAuthentication();

        phpCAS::getUser();

        return $this->redirect($response, 'home');
    }


    public function logout(Request $request, Response $response)
    {
        phpCAS::client(CAS_VERSION_2_0,'auth.univ-lorraine.fr',443,'');
        phpCAS::logout();
    }
}
