<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Slim\Exception\NotFoundException;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;
use Awurth\SlimValidation\Validator;
use Symfony\Component\Yaml\Yaml;

/**
 * @property Twig view
 * @property Router router
 * @property Messages flash
 * @property Validator validator
 */
abstract class Controller
{
    /**
     * Slim application container.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Data about the API (URL, username, password)
     *
     * @var array
     */
    protected $api;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->api = Yaml::parse(file_get_contents(__DIR__ . '../../../../app/config/parameters.yml'))['api'];
    }

    /**
     * Gets request parameters.
     *
     * @param Request  $request
     * @param string[] $params
     * @param string   $default
     *
     * @return string[]
     */
    public function params(Request $request, array $params, $default = null)
    {
        $data = [];
        foreach ($params as $param) {
            $data[$param] = $request->getParam($param, $default);
        }

        return $data;
    }

    /**
     * Redirects to a route.
     *
     * @param Request $request
     * @param Response $response
     * @param string   $route
     * @param array    $params
     *
     * @return Response
     */
    public function redirect(Request $request, Response $response, $route, array $params = [])
    {
        return $response->withRedirect($this->router->pathFor(
            $route,
            ['country' => $this->getCountry($request)] + $params
        ));
    }

    /**
     * Redirects to a url.
     *
     * @param Response $response
     * @param string   $url
     *
     * @return Response
     */
    public function redirectTo(Response $response, $url)
    {
        return $response->withRedirect($url);
    }

    /**
     * Writes JSON in the response body.
     *
     * @param Response $response
     * @param mixed    $data
     * @param int      $status
     *
     * @return Response
     */
    public function json(Response $response, $data, $status = 200)
    {
        return $response->withJson($data, $status);
    }

    /**
     * Writes text in the response body.
     *
     * @param Response $response
     * @param string   $data
     * @param int      $status
     *
     * @return int
     */
    public function write(Response $response, $data, $status = 200)
    {
        return $response->withStatus($status)->getBody()->write($data);
    }

    /**
     * Adds a flash message.
     *
     * @param string $name
     * @param string $message
     */
    public function flash($name, $message)
    {
        $this->flash->addMessage($name, $message);
    }

    /**
     * Gives the country id used by the client.
     *
     * @param Request $request
     *
     * @return string
     */
    public function getCountry(Request $request)
    {
        return $request->getAttribute('routeInfo')[2]['country'];
    }

    /**
     * Creates a new NotFoundException.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return NotFoundException
     */
    public function notFoundException(Request $request, Response $response)
    {
        return new NotFoundException($request, $response);
    }

    /**
     * Gets a service from the container.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->container->get($property);
    }

}
