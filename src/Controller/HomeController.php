<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use OpenLRW\OpenLRW;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{

    /**
     * Render the home page.
     *
     * @Route("/", name="home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(Request $request)
    {
        if (OpenLRW::isUp()) {
            return $this->render('home.twig');
        }


        return $this->render('Error/unavailable.twig');
    }

    /**
     * Leave the "View as" mode.
     *
     * @Route("/view/actions/leave", name="leave-view-as")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function leaveViewAs(Request $request)
    {
        $username = $_SESSION['username'];
        $result = $this->ldapFirst("uid=$username");

        $_SESSION['phpCAS']['user'] = $username;
        $_SESSION['name'] = $result['displayname'][0];
        $_SESSION['email'] = $result['mail'][0];
        unset($_SESSION['username']);
        return $this->redirectToRoute('home');
    }

    /**
     * Redirect to "View as" form page by giving back admin rights.
     *
     * @Route("/view/actions/new", name="new-view-as")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeViewAsTarget(Request $request)
    {
        $username = $_SESSION['username'];
        $result = $this->ldapFirst("uid=$username");

        $_SESSION['phpCAS']['user'] = $username;
        $_SESSION['name'] = $result['displayname'][0];
        $_SESSION['email'] = $result['mail'][0];
        unset($_SESSION['username']);
        return $this->redirectToRoute('view-as');
    }

}
