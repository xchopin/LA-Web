<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;


use Exception;
use OpenLRW\Exception\GenericException as OpenLrwException;
use OpenLRW\Exception\NotFoundException;
use OpenLRW\Model\Event;
use OpenLRW\Model\Klass;
use OpenLRW\Model\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\SessionUnavailableException;


class UserController extends AbstractController implements AuthenticatedInterface
{


    /**
     * Renders the profile of a student with several data from OpenLRW API.
     *
     * @Route("/me", name="profile")
     * @param Request $request
     * @return Response
     */
    public function profile(Request $request): ?Response
    {
        try {
            $user = User::find(self::loggedUser());


            //$events['all'] = User::eventsFrom(self::loggedUser(), date('Y-m-d H:i', strtotime('-1 week')));
            //$events['cas'] = null;
            //$events['moodle'] = null;

            //if ($events['all'] !== null) {
            //    usort($events['all'], static function ($a, $b) {
            //        return $a->eventTime < $b->eventTime;
            //    });

            //    for ($i = 7; $i >= 0; $i--) {
            //        $cas_events[date('Y-m-d', strtotime("-$i day"))] = [];
            //        $moodle_events[date('Y-m-d', strtotime("-$i day"))] = [];
            //    }

            //    foreach ($events['all'] as $event) {
            //        $date = date('Y-m-d', strtotime($event->eventTime));
            //        if ($event->object->{'@type'} === 'SoftwareApplication') {
            //            if (array_key_exists($date, $cas_events))
            //                $cas_events[$date][] = $event;
            //        } else {
            //            if (array_key_exists($date, $moodle_events))
            //                $moodle_events[$date][] = $event;
            //        }
            //    }

            //    $events['cas'] = $cas_events;
            //    $events['moodle'] = $moodle_events;
            //}

            return $this->render('User/profile.twig', [
                'givenName' => $user->givenName,
                'metadata' => $user->metadata,
                'enrollments' => $this->enrollments()
            ]);
        } catch (SessionUnavailableException $e) {
            return $this->redirectToRoute('login', 'profile');
        } catch (NotFoundException $e) {
            $this->addFlash('error', 'Student does not exist');
            return $this->redirectToRoute('home');
        }

    }


    /**
     * Give enrollments for a user given.
     *
     * @return array
     */
    public function enrollments(): array
    {
        $id = self::loggedUser();
        try {
            $enrollments = User::enrollments($id);
        } catch (NotFoundException $e) {
            return [];
        }

        $classes = [];

        if ($enrollments !== null) {
            foreach ($enrollments as $enrollment) {
                try {
                    $class = Klass::find($enrollment->class->sourcedId);
                    if ($class->title !== null && $class->status === 'active' ) {
                        $enrollment->title = $class->title;
                        $classes[] = $enrollment;
                    }
                } catch (NotFoundException $e) {

                }

            }

            usort($classes, static function($a, $b) { // ASC Sort
                return strtolower($a->title) > strtolower($b->title);
            });
        }

        return $classes;
    }



    /**
     * Return the settings of a user.
     *
     * @Route("/me/settings", name="get_settings", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function userSettings(Request $request): Response
    {

        $id = self::loggedUser();
        $user = User::find($id);
        $metadata = $user->metadata;
        $settings = [];

        foreach ($metadata as $key => $value) {
            if (strpos($key, 'settings') === 0) {
                $settings[] = array($key, $value);
            }
        }

        return $this->json($settings);
    }




    /**
     * Disable user account
     *
     * @Route("/disable-account", name="disable-account")
     * @param Request $request
     * @return RedirectResponse
     */
    public function disableAccount(Request $request): RedirectResponse
    {
        $id    = self::loggedUser();
        $user  = User::find($id);
        $user->enabledUser = false;
        $user->save();

        $this->setGdprAgreement($user, false);
        return $this->redirectToRoute('logout');
    }

    /**
     * Update the settings of a user.
     *
     * @Route("/api/users/settings", name="edit_settings", methods={"POST"})
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function editSettings(Request $request)
    {
        try {
            $key   = $request->get('key');
            $value = $request->get('value');
            $id    = self::loggedUser();
            $user  = User::find($id);
            $status = $this->editAttributeUserMetadata($user, $key, $value);

            return new Response($status);
        } catch (Exception $e) {
            return new Response($e->getMessage(), 404);
        }
    }

    /**
     * Create a Caliper event when user changes their personal objective
     *
     * @Route("/api/users/personal-objective", name="edit_personal_objective", methods={"POST"})
     * @param Request $request
     *
     * @return bool|Response
     */
    public function changePersonalObjective(Request $request)
    {
        try {
           if ($_SESSION['isAdmin'] || isset($_SESSION['username'])) {
               return false;  // Check if it's an admin (even in as view mode)
           }
            $value  = $request->get('personalObjective');
            $route  = $request->getPathInfo();
            $userId = self::loggedUser();

            Event::caliperFactory($userId, 'Modified', "personalObjective-$value", $route);

            return new Response(201);
        } catch (Exception $e) {
            return new Response($e->getMessage(), 404);
        }
    }



}
