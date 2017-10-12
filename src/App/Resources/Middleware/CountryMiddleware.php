<?php

namespace App\Resources\Middleware;

use App\Resources\TwigExtension\PathTranslationExtension;
use Slim\Http\Request;
use Slim\Http\Response;

class CountryMiddleware extends Middleware
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        // First visit we try to set the language from the browser settings
        $country_id = isset($_COOKIE['country']) ? $request->getAttribute('routeInfo')[2]['country']: substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

        setcookie(
            'country',
            $country_id,
            time() + (30 * 24 * 60 * 60) // 30 days
        );

        $this->view->addExtension(new PathTranslationExtension($response, $request, $this->container['router']));

        return $next($request, $response);
    }
}
