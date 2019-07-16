<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Event\AdminSubscriber;
use OpenLRW\Model\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use phpCAS;

class AuthController extends AbstractController
{

    /**
     * Redirects to the CAS authentication page.
     *
     * @Route("/login", name="login")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(Request $request)
    {

        phpCAS::client(CAS_VERSION_2_0, getenv('CAS_HOST'), (int)getenv('CAS_PORT'), '');
        phpCAS::setNoCasServerValidation();
        phpCAS::forceAuthentication();
        phpCAS::getUser();

        $username = $_SESSION['phpCAS']['user'];
        $result = $this->ldapFirst("uid=$username");
        $user = User::find($username);

        $session = $request->getSession();
        $session->set('rulesAgreement', $user->status === 'active');

        $_SESSION['name'] = $result['displayname'][0];
        $_SESSION['email'] = $result['mail'][0];
        $_SESSION['isAdmin'] = false; // initialize


        if (getenv('APP_ENV') === 'dev') { // If the app is in dev mode, only admin can log in
            $adminSubscriber = new AdminSubscriber($this->container);
            if (!$adminSubscriber->isAdmin()) {
                $session->clear();
                session_destroy();
                $this->addFlash('error', 'You are not allowed to log in.');
                return $this->redirectToRoute('home');
            }
        }

        if (isset($_GET['redirect'])) {
            return $this->redirectToRoute($_GET['redirect']);
        }

        return $this->redirectToRoute('home');

    }

    /**
     * Logs Out by destroying the CAS Session then redirects to the home page.
     *
     * @Route("/logout", name="logout")
     * @param Request $request
     */
    public function logout(Request $request)
    {
        session_destroy();
        phpCAS::client(CAS_VERSION_2_0, getenv('CAS_HOST'), (int)getenv('CAS_PORT'), '');
        phpCAS::logout();
    }
}
