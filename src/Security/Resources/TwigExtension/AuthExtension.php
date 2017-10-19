<?php

namespace Security\Resources\TwigExtension;

use Twig_Extension;
use Twig_SimpleFunction;
use Slim\Container;
class AuthExtension extends Twig_Extension
{
    /**
     * @var LDAP Instance
     */
    protected $ldap;

    /**
     * @var string Base Distinguished Name
     */
    protected $baseDN;


    public function __construct($ldap, $baseDN)
    {
        $this->ldap = $ldap;
        $this->baseDN = $baseDN;
    }

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
        $name = null;

        if (isset($_SESSION['phpCAS']['user'])) {
            $query = ldap_search($this->ldap, $this->baseDN, 'uid=' . $_SESSION['phpCAS']['user']);
            $name = ldap_get_entries ($this->ldap, $query)[0]['displayname'][0];
        }

        return (object)
        [
            'isLogged' => isset($_SESSION['phpCAS']['user']) ? true : false,
            'username' => isset($_SESSION['phpCAS']['user']) ? $_SESSION['phpCAS']['user'] : null,
            'name' => $name
        ];
    }
}
