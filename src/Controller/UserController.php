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

        //ToDo: separate with ajax calls
        $classes = [];
        $id = $_SESSION['phpCAS']['user'];

        try {
            $user = User::find($id);
        } catch (Exception $e) {
            return $this->render('Error/unavailable.twig');
        }

        if ($user === null) {
            $this->addFlash('error', "Student `$id` does not exist");
            return $this->redirectToRoute('home');
        }

        $events = User::events($id);

        $activities = [];

        if ($events != null) {
            foreach ($events as $event)
                array_push($activities, $event->object->name);

            $activities = array_count_values($activities);
        }


        //foreach (json_decode($enrollments->getBody()->getContents()) as $enrollment) {
        //    if ($enrollment->class->title != null)
        //        array_push($classes, $enrollment->class);
        //}

        return $this->render('User/profile.twig', [
            'givenName' => $user->givenName,
            'classes' => null,
            'events' => $events,
            'event_activities' => json_encode($activities, JSON_UNESCAPED_SLASHES )
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

}