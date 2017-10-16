<?php

namespace App\Resources\Middleware;

use App\Resources\TwigExtension\MultilingualPathExtension;
use App\Resources\TwigExtension\TranslatorExtension;
use Slim\Http\Request;
use Slim\Http\Response;

class CountryMiddleware extends Middleware
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $country_id = $request->getAttribute('routeInfo')[2]['country'];

        // Useful when a client edits the URL and types a language that does not exist.
        if (!$this->checkCountry($country_id))
            return $response->withRedirect($this->router->pathFor('wrong-entry'));

        setcookie('country', $country_id, time() + (30 * 24 * 60 * 60)); // 30 days

        $this->view->addExtension(new MultilingualPathExtension($response, $request, $this->container['router']));
        $this->view->addExtension(new TranslatorExtension($request, $country_id));

        return $next($request, $response);
    }

    /**
     * Checks for a country id given if a dictionary is associated.
     *
     * @param string $country_id
     * @return bool
     */
    private function checkCountry($country_id)
    {
        return file_exists(dirname(__FILE__) . '/../../' . DICTIONARY_PATH . ''. $country_id . '.json');
    }
}
