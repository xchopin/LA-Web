<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Exception;
use OpenLRW\Model\Klass;
use OpenLRW\Model\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\SessionUnavailableException;

class ClassController extends AbstractController
{

    /**
     * Show events for a class and a user given.
     *
     * @Route("/classes/{id}", name="class")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function class(Request $request, String $id = ''): Response
    {
        $class = Klass::find($id);

        if ($class === null) {
            $this->addFlash('error', 'Class does not exist');
            return $this->redirectToRoute('home');
        }

        $userId = self::loggedUser();
        $enrollments = User::enrollments($userId);

        foreach ($enrollments as $enrollment){
                if ($class->sourcedId === $enrollment->class->sourcedId) { // Check if the user is enrolled to this class
                    if ($enrollment->role === 'student') {
                        $events = Klass::eventsForUser($id, self::loggedUser());
                        if ($events !== null)
                            usort($events, static function($a, $b) {return $a->eventTime < $b->eventTime;});

                        return $this->render('User/Class/student_class.twig', [
                            'class' => $class,
                            'events' => $events
                        ]);
                    }

                    return $this->render('User/Class/professor_class.twig', [
                        'class' => $class
                    ]);
                }
        }

        $this->addFlash('error', 'You are not enrolled in this class.');
        return $this->redirectToRoute('home');
    }

    /**
     * Return results for a class and a user given.
     *
     * @Route("/classes/{id}/results", name="class-results")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function classResults(Request $request, String $id = ''): ?Response
    {
        try {
            $results = Klass::resultsForUser($id, self::loggedUser());
            $lineItems = Klass::lineItems($id);
            $res = []; $i = 0;
            foreach ($results as $result) {
                $res[$i]['date'] = $result->date;
                $res[$i]['score'] = $result->score;
                foreach ($lineItems as $lineItem) {
                    if ($lineItem->sourcedId === $result->lineitem->sourcedId)
                        $res[$i]['title'] = $lineItem->title;
                } $i++;
            }

            return $this->json($res);
        }catch (Exception $e) {
            return new Response($e->getMessage(), 404);
        }

    }

}