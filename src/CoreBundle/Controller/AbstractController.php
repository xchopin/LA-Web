<?php

namespace CoreBundle\Controller;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

 class AbstractController extends Controller
{

    /**
     * Guzzle HTTP client instance linked to the OpenLRW API
     *
     * @var Client
     */
    protected $http;

    /**
     * Settings for the API
     *
     * @var array
     */
    private $apiSettings;

    /**
     * Constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->apiSettings = $container->getParameter('api');
        $this->http = new Client(['base_uri' => $this->apiSettings['uri']]);
    }

    /**
     * Creates and return a JSON Web Token through the OpenLRW API by using credentials filled in parameters.yml
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function createJWT()
    {
        return json_decode( $this->http->request('POST', 'auth/login', [
            'headers' => [ 'X-Requested-With' => 'XMLHttpRequest' ],
            'json' => [
                'username' => $this->apiSettings['username'],
                'password' => $this->apiSettings['password']
            ]
        ])->getBody()
          ->getContents());
    }

    /**
     * Stops the script and prints info about a variable
     *
     * @param mixed $variable
     */
    public function debug($variable)
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
    public function searchLDAP($filter, $arg = [])
    {
        return ldap_search($this->__get('ldap'), $this->getParameter('ldap')['base_dn'], $filter, $arg);
    }

    /**
     * Returns data from a LDAP query
     *
     * @param $filter
     * @param array $arg
     * @return mixed
     */
    public function ldap($filter, $arg = [])
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
    public function ldapFirst($filter, $arg = [])
    {
        return ldap_get_entries($this->__get('ldap'), $this->searchLDAP($filter, $arg))[0];
    }


}