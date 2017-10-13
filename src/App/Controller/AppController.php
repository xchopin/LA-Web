<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
Use App\Model\User;

class AppController extends Controller
{

    public function redirectHome(Request $request, Response $response)
    {
        $exists = false;
        $clientLanguage = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

        // First if the client has a preference
        if (isset($_COOKIE['country'])) {
            $country_id= $_COOKIE['country'];
            $exists = $this->checkCountry($country_id);
        } else if ($this->checkCountry($clientLanguage)) { // Not a preference? Let's check its browser!
            $country_id = $clientLanguage;
        } else {  // Nevermind let's move to the french language
            $country_id = 'fr';
        }

        return $response->withRedirect($this->router->pathFor('home', ['country' => $country_id]));
    }

    public function home(Request $request, Response $response)
    {
        return $this->view->render($response, 'App/home.twig');
    }

    public function getUsers(Request $request, Response $response)
    {
        return $this->view->render($response, 'App/users.twig', ['users' => User::limit(50)->get()]);
    }

    /**
     * Checks for a country id given if a dictionary is associated.
     *
     * @param string $country_id
     * @return bool
     */
    private function checkCountry($country_id)
    {
        return file_exists(dirname(__FILE__) . '/../' . DICTIONARY_PATH . ''. $country_id . '.json');
    }
}
