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

    /**
     * Constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        self::$http = new Client(['base_uri' => env('API_URI')]);
        $container->set('http', self::$http);
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
        return ldap_search($this->__get('ldap'), env('LDAP_BASE_DN'), $filter, $arg);
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
        return ldap_get_entries($this->__get('ldap'), $this->searchLDAP($filter, $arg));
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


}