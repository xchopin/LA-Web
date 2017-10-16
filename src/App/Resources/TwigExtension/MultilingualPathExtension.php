<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Resources\TwigExtension;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class PathTranslationExtension
 * It aims to give the URL path for a route name given without adding the country id (which is bothering)
 * @package App\Resources\TwigExtension
 */
class MultilingualPathExtension extends Twig_Extension
{

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var string
     */
    protected $country_id;

    public function __construct(Response $response, Request $request, Router $router)
    {
        $this->response = $response;
        $this->request = $request;
        $this->country_id = $request->getAttribute('routeInfo')[2]['country'];
        $this->router = $router;
    }

    public function getName()
    {
        return 'path';
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('path', [$this, 'path'])
        ];
    }

    public function path($route, array $params = [])
    {
        return $this->router->pathFor($route, ['country' => $this->country_id] + $params);
    }
}
