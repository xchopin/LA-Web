<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

use App\Controller\AbstractController as Provider;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class OneRoster
{

    /**
     * Generic function to GET /api/ OpenLRW routes
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
                'api/' . $route,
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
            } else if ($e->getCode() == 404) {
                return null;
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

    /**
     * Generic function to send HTTP PATCH requests
     *
     * @param String $route
     * @param String $json
     * @return mixed|null
     * @throws \Exception
     */
    public function patch(String $route, String $json)
    {
        try {
            return Provider::$http->patch("api/$route", [
                    'headers' => [
                        'X-Requested-With' => 'XMLHttpRequest',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . Provider::makeJWT()
                    ],
                    'body' => $json
                ])
                ->getStatusCode();
        } catch (GuzzleException $e) {
            if ($e->getCode() == 401) {
                try {
                    Provider::generateJwt();
                } catch (GuzzleException $e) {
                    die($e->getMessage());
                }
                self::get($route);
            } else if ($e->getCode() == 404) {
                return null;
            } else {
                throw new \Exception($e);
            }
            return null;
        }
    }




}