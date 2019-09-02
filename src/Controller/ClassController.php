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
use OpenLRW\Model\Klass;
use OpenLRW\Model\Risk;
use OpenLRW\Model\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClassController extends AbstractController
{

    /**
     * Show events for a class and a user given.
     *
     * @Route("/classes/{id}/{date}", name="class")
     * @param Request $request
     * @param String $id
     * @param String|null $date
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function class(Request $request, String $id = '', String $date = null): Response
    {

        try {
            $class = Klass::find($id);
        }catch (OpenLrwException $e) {
            $this->addFlash('error', 'Class does not exist');
            return $this->redirectToRoute('home');
        }

        $userId = self::loggedUser();
        $enrollments = User::enrollments($userId);

        foreach ($enrollments as $enrollment){
            if (self::isProfessorModeEnabled()) { // If "Professor Mode" is On, render the view
                return $this->render('User/Class/professor_class.twig', [
                    'class' => $class
                ]);
            }

            if ($class->sourcedId === $enrollment->class->sourcedId) { // Check if the user is enrolled to this class

                if ($enrollment->role === 'student') {
                    return $this->studentClass($class, $date);
                }

                // Else user is teacher of this class
                return $this->render('User/Class/professor_class.twig', ['class' => $class]);
            }
        }

        $this->addFlash('error', 'You are not enrolled in this class.');
        return $this->redirectToRoute('home');
    }


    /**
     * Data treatment for the student class view
     *
     * @param $class
     * @param $date
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    private function studentClass(Klass $class, $date)
    {
            $userId = self::loggedUser();
            $classId = $class->sourcedId;

            try {
                if ($date === null) {
                    $risk = Risk::latestByClassAndUser($classId, $userId);
                    $date = date('Y-m-d', strtotime($risk->dateTime));
                } else {
                    $risk = Risk::findByClassAndUserAndDate($classId, $userId, $date);
                    $date = date('Y-m-d', strtotime($date));
                }
            } catch (Exception $e) {
                return $this->render('User/Class/student_class.twig', [
                    'date' => $date,
                    'class' => $class,
                    'scores' => null
                ]);
            }

            // - - - Risk treatment - - -
            $indicators = [];
            $scores = [];
            $tempWeight = [];
            $weight = [];

            foreach ($risk->metadata as $attribute => $value) {
                if (strpos($attribute, 'global') !== 0 ) {
                    if (stripos($attribute, 'Weight')) {
                        $tempWeight[] = (float)$value;
                    }else{
                        if (strpos($value, '/')) {
                            $explode = explode('/', $value);
                            $value = round(($explode[0] / $explode[1]) * 100, 1);
                        }else{
                            $value = round($value * 100, 1);
                        }
                        $indicators[$attribute] = $value;
                    }
                } else  {
                    $scores = $this->extractScoreFromMetadataAttribute($scores, $attribute, $value);
                }
            }


            // Calculate the radian factor
            $res = 2 * M_PI / array_sum($tempWeight);

            foreach ($tempWeight as $w) {
                $weight[] = $w * $res;
            }


            $scores = $this->userWeekScores($date, $risk,   $userId);


            return $this->render('User/Class/student_class.twig', [
                    'date' => $date,
                    'class' => $class,
                    'scores' => $scores,
                    'indicators' => $indicators,
                    'weight' => $weight
            ]);


        /**$events = Klass::eventsForUser($id, self::loggedUser());
        if ($events !== null) {
            usort($events, static function ($a, $b) {
                return $a->eventTime < $b->eventTime;
            });
        }*/

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
                    if ($lineItem->sourcedId === $result->lineitem->sourcedId) {
                        $res[$i]['title'] = $lineItem->title;
                    }
                } $i++;
            }

            return $this->json($res);
        }catch (Exception $e) {
            return new Response($e->getMessage(), 404);
        }

    }

    /**
     * Get the scores of a user for the 7 last days
     *
     * @param $initDate
     * @param $firstRisk
     * @param $userId
     * @return array
     */
    private function userWeekScores(string $initDate, Risk $firstRisk, string $userId): array
    {
        $weekScores[$initDate] = $this->extractScoresFromRisk($firstRisk);

        for ($i = 1; $i < 8; $i++) {
            $day = date('Y-m-d', strtotime("-$i day", strtotime($initDate)));

            try {
                $risk = Risk::findByClassAndUserAndDate('23133', $userId, $day);
                $dayScores = $this->extractScoresFromRisk($risk);
            } catch (NotFoundException $e) {
                $dayScores = null;
            }

            $weekScores[$day] = $dayScores;
        }

        return $weekScores;
    }


    /**
     * Extract all the scores from the metadata attribute of a Risk object
     *
     * @param Risk $risk
     * @return array
     */
    private function extractScoresFromRisk(Risk $risk): array
    {
        $scores = [];
        foreach ($risk->metadata as $attribute => $value) {
            $scores = $this->extractScoreFromMetadataAttribute($scores, $attribute, $value);
        }

        return $scores;
    }


    /**
     * Check if the metadata attribute is a score and then place it in an array
     *
     * @param array $scores
     * @param string $attribute
     * @param $value
     * @return array
     */
    private function extractScoreFromMetadataAttribute(array $scores, $attribute, $value): array
    {
        if (strpos($attribute, 'global') === 0 ) {
            $trimmed = preg_replace('/\D/', '', $attribute); // get int value
            $arr = explode('/', $value, 2); // cut the string in two parts
            $value = $arr[0]; // take the first part of the string
            $scores[$trimmed] = $value; // already a percentage
        }

        return $scores;
    }

}