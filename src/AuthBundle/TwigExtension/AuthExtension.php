<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AuthBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;
use Twig_SimpleFunction;

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
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->ldap = $container->get('ldap');
        $this->baseDN = $container->getParameter('ldap')['base_dn'];
        $this->administrators = $container->getParameter('administrators');
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
