<?php

namespace Security\Resources\TwigExtension;

use Twig_Extension;
use Twig_SimpleFunction;
use Slim\Container;
class AuthExtension extends Twig_Extension
{
    /**
     * @var resource LDAP Instance
     */
    protected $ldap;

    /**
     * @var string Base Distinguished Name
     */
    protected $baseDN;

    /**
     * @var array List of the super admin written in app/config/parameters.yml
     */
    protected $administrators;

    /**
     * AuthExtension constructor.
     * @param resource $ldap
     * @param string $baseDN
     * @param array $administrators
     */
    public function __construct($ldap, $baseDN, $administrators)
    {
        $this->ldap = $ldap;
        $this->baseDN = $baseDN;
        $this->administrators = $administrators;
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
        $logged = false;
        $isAdmin = false;
        $username = null;

        if (isset($_SESSION['phpCAS']['user'])) {
            $logged = true;
            $username = $_SESSION['phpCAS']['user'];
            $query = ldap_search($this->ldap, $this->baseDN, "uid=$username");
            $name = ldap_get_entries($this->ldap, $query)[0]['displayname'][0];
            $isAdmin = (in_array($username, $this->administrators));
        }

        return (object)
        [
            'isLogged' => $logged,
            'isAdmin' =>  $isAdmin,
            'username' => $username,
            'name' => $name
        ];
    }
}
