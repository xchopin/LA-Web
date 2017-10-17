<?php

namespace Security\Resources\TwigExtension;

use Psr\Http\Message\ServerRequestInterface;
use Twig_Extension;
use Twig_SimpleFunction;
use Symfony\Component\Yaml\Yaml;
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
        $parameters = Yaml::parse(file_get_contents(__DIR__ . '../../../../../app/config/parameters.yml'))['ldap'];
        $ldapInstance = ldap_connect($parameters['host'], $parameters['port']);
        $name = null;

        if (isset($_SESSION['phpCAS']['user'])) {
            $query = ldap_search ($ldapInstance, $parameters['base_dn'], $parameters['filter'] . $_SESSION['phpCAS']['user']);
            $name = ldap_get_entries ($ldapInstance, $query)[0]['displayname'][0];
        }

        return (object)
        [
            'isLogged' => isset($_SESSION['phpCAS']['user']) ? true : false,
            'username' => isset($_SESSION['phpCAS']['user']) ? $_SESSION['phpCAS']['user'] : null,
            'name' => $name
        ];
    }
}
