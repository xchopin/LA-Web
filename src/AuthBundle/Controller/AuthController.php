<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AuthBundle\Controller;

use CoreBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use phpCAS;

class AuthController extends AbstractController
{
    
    /**
     * Redirects to the CAS authentication page.
     *
     * @Route("/login", name="login")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login()
    {
        $settings = $this->getParameter('cas');
        phpCAS::client(CAS_VERSION_2_0, $settings['host'], $settings['port'], '');
        phpCAS::setNoCasServerValidation();
        phpCAS::forceAuthentication();
        phpCAS::getUser();

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
        $settings = $this->getParameter('cas');
        phpCAS::client(CAS_VERSION_2_0, $settings['host'], $settings['port'], '');
        phpCAS::logoutWithRedirectService('http://' . $request->getBaseUrl());
    }
}
