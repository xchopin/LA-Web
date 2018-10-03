<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Model\API\Klass;
use App\Model\API\User;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController implements AuthenticatedInterface
{


    /**
     * Renders the profile of a student with several data from OpenLRW API.
     *
     * @Route("/me", name="profile")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profile(Request $request)
    {
        $user = User::find(self::loggedUser());

        if ($user === null) {
            $this->addFlash('error', "Student does not exist");
            return $this->redirectToRoute('home');
        }

        $events['all'] = User::eventsFrom(self::loggedUser(), date('Y-m-d H:i', strtotime("-1 week")));

        $events['cas'] = null;
        $events['moodle'] = null;

        if ($events['all'] != null) {
            usort($events['all'], function ($a, $b) {
                return $a->eventTime < $b->eventTime;
            });

            for ($i = 7; $i >= 0; $i--) {
                $cas_events[date('Y-m-d', strtotime("-$i day"))] = [];
                $moodle_events[date('Y-m-d', strtotime("-$i day"))] = [];
            }

            foreach ($events['all'] as $event) {
                $date = date('Y-m-d', strtotime($event->eventTime));
                if ($event->object->{'@type'} == 'SoftwareApplication') {
                    if (array_key_exists($date, $cas_events))
                        array_push($cas_events[$date], $event);
                } else {
                    if (array_key_exists($date, $moodle_events))
                        array_push($moodle_events[$date], $event);
                }
            }

            $events['cas'] = $cas_events;
            $events['moodle'] = $moodle_events;

        }


        return $this->render('User/profile.twig', [
            'givenName' => $user->givenName,
            'events' => $events
        ]);

    }


    /**
     * Give enrollments for a user given.
     *
     * @Route("/me/enrollments", name="enrollments")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enrollments(Request $request)
    {
        $id = $_SESSION['phpCAS']['user'];
        $classes = [];

        $enrollments = User::enrollments($id);

        if ($enrollments != null) {
            foreach ($enrollments as $enrollment) {
                $class = Klass::find($enrollment->class->sourcedId);
                isset($class->title) ? $enrollment->title = $class->title : $enrollment->title = 'null';
                array_push($classes, $enrollment);
            }
        } else {
            return new Response('Enrollments not found.', 404);
        }

        usort($classes, function($a, $b) { // ASC Sort
            return strtolower($a->title) > strtolower($b->title);
        });


        return $this->json($classes);
    }


    /**
     * Show events for a class and a user given.
     *
     * @Route("/classes/{id}", name="class")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function class(Request $request, String $id = '')
    {
        $class = Klass::find($id);

        if ($class == null) {
            $this->addFlash('error', 'Class does not exist');
            return $this->redirectToRoute('home');
        }

        $events = Klass::eventsForUser($id, self::loggedUser());

        if ($events != null)
            usort($events, function($a, $b) {return $a->eventTime < $b->eventTime;});

        return $this->render('User/class.twig', [
            'class' => $class,
            'events' => $events
        ]);

    }

    /**
     * Return results for a class and a user given.
     *
     * @Route("/classes/{id}/results", name="class-results")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function classResults(Request $request, String $id = '')
    {
        try {
            $results = Klass::resultsForUser($id, self::loggedUser());
            $lineItems = Klass::lineItems($id);
            $res = []; $i = 0;
            foreach ($results as $result) {
                $res[$i]['date'] = $result->date;
                $res[$i]['score'] = $result->score;
                foreach ($lineItems as $lineItem) {
                    if ($lineItem->sourcedId == $result->lineitem->sourcedId)
                        $res[$i]['title'] = $lineItem->title;
                } $i++;
            }

            return $this->json($res);
        }catch (Exception $e) {
            return new Response($e->getMessage(), 404);
        }

    }

}