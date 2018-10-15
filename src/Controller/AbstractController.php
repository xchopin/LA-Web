<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

 abstract class AbstractController extends Controller
{

    /**
     * Guzzle HTTP client instance linked to the OpenLRW API
     *
     * @var Client
     */
    static public $http;

    static private $ldap;

    static private $baseDN;

    /**
     * Constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        self::$http = new Client(['base_uri' => env('API_URI')]);
        self::$ldap = ldap_connect(env('LDAP_HOST'), env('LDAP_PORT'));
        self::$baseDN = env('LDAP_BASE_DN');
        ldap_set_option(self::$ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option(self::$ldap, LDAP_OPT_REFERRALS, 0);

        $container->set('http', self::$http);
        $container->set('ldap', /** @scrutinizer ignore-type */ self::$ldap);
    }

     /**
      * Creates and return a JSON Web Token through the OpenLRW API by using credentials filled in .env
      *
      * @return mixed|\Psr\Http\Message\ResponseInterface
      * @throws \GuzzleHttp\Exception\GuzzleException
      */
    public static function generateJwt()
    {
        $_SESSION['JWT'] = json_decode( self::$http->request('POST', 'api/auth/login', [
            'headers' => [ 'X-Requested-With' => 'XMLHttpRequest' ],
            'json' => [
                'username' => env('API_USERNAME'),
                'password' => env('API_PASSWORD')
            ]
        ])->getBody()
          ->getContents())->token;

        return $_SESSION['JWT'];

    }


    protected static function getJwt()
    {
        return $_SESSION['JWT'];
    }

    static function makeJwt()
    {
        return isset($_SESSION['JWT']) ? self::getJwt() : self::generateJwt();
    }

    /**
     * Stops the script and prints info about a variable
     *
     * @param mixed $variable
     */
    static protected function debug($variable)
    {
        die('<pre>' . print_r($variable, true) . '</pre>');
    }


    /**
     * Gets a service from the container.
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
     * Executes a LDAP query
     *
     * @param $filter
     * @param array $arg
     * @return resource
     */
    protected function searchLDAP($filter, $arg = [])
    {
        return ldap_search(self::$ldap, self::$baseDN, $filter, $arg);
    }

    /**
     * Returns data from a LDAP query
     *
     * @param $filter
     * @param array $arg
     * @return mixed
     */
    protected function ldap($filter, $arg = [])
    {
        return ldap_get_entries(self::$ldap, $this->searchLDAP($filter, $arg));
    }

    /**
     * Returns the first tuple from a LDAP query
     *
     * @param $filter
     * @param array $arg
     * @return mixed
     */
    protected function ldapFirst($filter, $arg = [])
    {
        return ldap_get_entries($this->__get('ldap'), $this->searchLDAP($filter, $arg))[0];
    }

     /**
      * Function to check if OpenLRW is up
      *
      * @return boolean
      * @throws \GuzzleHttp\Exception\GuzzleException
      */
     public static function isUp()
     {
         return self::$http->request('GET', '/info.json')->getStatusCode() == 200;
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


}