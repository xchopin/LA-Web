<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Model\API\Klass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;



class UserController extends AbstractController implements AuthenticatedInterface
{

    /**
     * Shows events for a class and user given
     *
     * @Route("/classes/{id}", name="class-events")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function classEvents(Request $request, $id = '')
    {

        //$events = Klass::events($id);
        //var_dump($events);
        return $this->render('User/class.twig', [
            'givenName' => null,
            'classes' => null,
            'events' => null,
            'event_activities' => null
        ]);
    }

}