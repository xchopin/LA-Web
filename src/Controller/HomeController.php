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
            // Did the user has accepted the rules?
            if ($request->getSession()->get('rulesAgreement') === false) {
                return $this->rulesAgreement($request);
            }

            return $this->redirectToRoute('profile');
        }
        return $this->render('Error/unavailable.twig');
    }

    /**
     * Render the rules agreement page.
     *
     * @Route("/agreement", name="rules-agreement")
     * @param Request $request
     * @return Response
     */
    public function rulesAgreement(Request $request): Response
    {
        return $this->render('rules-agreement.twig');
    }


    /**
     * Accept the agreement.
     *
     * @Route("/accept-agreement", name="accept-agreement")
     * @param Request $request
     * @return Response
     */
    public function acceptAgreement(Request $request): Response
    {
        $user = User::find(self::loggedUser());
        $user->status = 'active';
        $user->save();
        $request->getSession()->set('rulesAgreement', true);

        return $this->redirectToRoute('home');
    }


}
