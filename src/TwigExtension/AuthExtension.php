<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class AuthExtension extends Twig_Extension
{
    /**
     *
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
        $ldapInstance = ldap_connect(env('LDAP_HOST'), env('LDAP_PORT'));
        ldap_set_option($ldapInstance, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapInstance, LDAP_OPT_REFERRALS, 0);
        $container->set('ldap', /** @scrutinizer ignore-type */ $ldapInstance);
        $this->ldap = $ldapInstance;
        $this->baseDN = env('LDAP_BASE_DN');
        $this->administrators = explode(',', env('ADMINISTRATORS'));
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
