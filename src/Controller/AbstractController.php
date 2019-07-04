<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OpenLRW\OpenLRW;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Dotenv\Dotenv;

abstract class AbstractController extends Controller
{

    protected $openLRW;
    protected $ldap;
    protected $baseDN;


    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env'); // For Linux Servers

        $this->openLRW = new OpenLRW(getenv('API_URI'), getenv('API_USERNAME'), getenv('API_PASSWORD'));
        $this->ldap = ldap_connect(getenv('LDAP_HOST'), getenv('LDAP_PORT'));
        $this->baseDN = getenv('LDAP_BASE_DN');

        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
        
        ldap_bind($this->ldap, getenv('LDAP_USERNAME'), getenv('LDAP_PASSWORD'));

        $container->set('ldap', (object) $this->ldap);
    }


    /**
     * Stop the script and print info about a variable given.
     *
     * @param mixed $variable
     */
    protected function debug($variable)
    {
        die('<pre>' . print_r($variable, true) . '</pre>');
    }


    /**
     * Get a service from the container.
     *
     * @param string $service
     *
     * @return mixed
     */
    public function __get($service)
    {
        return $this->container->get($service);
    }

    /**
     * Execute a LDAP query.
     *
     * @param $filter
     * @param array $arg
     * @return resource
     */
    protected function searchLDAP($filter, $arg = [])
    {
        return ldap_search($this->ldap, $this->baseDN, $filter, $arg);
    }

    /**
     * Return data from a LDAP query.
     *
     * @param $filter
     * @param array $arg
     * @return mixed
     */
    protected function ldap($filter, $arg = [])
    {
        return ldap_get_entries($this->ldap, $this->searchLDAP($filter, $arg));
    }

    /**
     * Return the first tuple from a LDAP query.
     *
     * @param $filter
     * @param array $arg
     * @return mixed
     */
    protected function ldapFirst($filter, $arg = [])
    {
        return ldap_get_entries($this->ldap, $this->searchLDAP($filter, $arg))[0];
    }

     /**
      * Get the logged username
      *
      * @return mixed
      */
     public static function loggedUser()
     {
         return $_SESSION['phpCAS']['user'];
     }

    /**
     * Helper for knowing if professor mode is enabled
     *
     * @return bool
     */
     public static function isProfessorModeEnabled(): bool
     {
         return isset($_SESSION['professorMode']);
     }

}
