<?php

namespace App\Resources\TwigExtension;

use Psr\Http\Message\ServerRequestInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class AuthExtension extends Twig_Extension
{


    public function getName()
    {
        return 'auth';
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('auth', [$this, 'auth'])
        ];
    }

    public function auth()
    {
        return (object)
        [
            'isLogged' => isset($_SESSION['phpCAS']['user']) ? true : false,
            'username' => isset($_SESSION['phpCAS']['user']) ? $_SESSION['phpCAS']['user'] : null
        ];
    }
}
