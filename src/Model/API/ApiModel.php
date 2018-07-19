<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\API;

use App\Controller\AbstractController as Provider;
use GuzzleHttp\Exception\GuzzleException;

abstract class ApiModel
{

    /**
     * Generic function to GET OpenLRW routes
     *
     * @param String $route
     * @return mixed|object
     * @throws \Exception
     */
    public static function get(String $route)
    {
        try {
            return json_decode(Provider::$http->request(
                'GET',
                "api/$route",
                [
                    'headers' => [
                        'X-Requested-With' => 'XMLHttpRequest',
                        'Authorization' => 'Bearer ' . Provider::makeJWT()
                    ]
                ])
                ->getBody()
                ->getContents()
            );
        } catch (GuzzleException $e) {
            if ($e->getCode() == 401) {
                try {
                    Provider::generateJwt();
                } catch (GuzzleException $e) {
                    die($e->getMessage());
                }
                self::get($route);
            } else {
                throw new \Exception($e);
            }
            return null;
        }

    }

    /**
     * Generic function to POST OpenLRW routes
     *
     * @param String $route
     * @param array $args
     * @return mixed|object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post(String $route, array $args) // ToDo : add JWT
    {
       return json_decode( self::$http->request('POST', $route, [
           'headers' => [ 'X-Requested-With' => 'XMLHttpRequest' ],
           'json' => $args
       ])->getBody()->getContents());
    }

}