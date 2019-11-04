<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use OpenLRW\Model\User;
use OpenLRW\OpenLRW;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{

    /**
     * Render the home page.
     *
     * @Route("/", name="home")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        if (OpenLRW::isUp()) {
            // Did the user has accepted the rules? - this value is added to the session on the login
            if ($request->getSession()->get('gdprAgreement') === false) {
                return $this->gdprAgreement($request);
            }

            return $this->redirectToRoute('profile');
        }
        return $this->render('Error/unavailable.twig');
    }

    /**
     * Render the GDPR agreement page.
     *
     * @Route("/gdpr", name="gdpr-agreement")
     * @param Request $request
     * @return Response
     */
    public function gdprAgreement(Request $request): Response
    {
        return $this->render('gdpr-agreement.twig');
    }


    /**
     * Accept the GDPR agreement.
     *
     * @Route("/accept-agreement", name="accept-agreement")
     * @param Request $request
     * @return Response
     */
    public function acceptAgreement(Request $request): Response
    {
        $user = User::find(self::loggedUser());
        $user->userEnabled = true;
        $user->save();
        $request->getSession()->set('isGdprAccepted', true);
        $this->setGdprAgreement($user, true);


        return $this->redirectToRoute('home');
    }



}
