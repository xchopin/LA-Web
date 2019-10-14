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
use OzdemirBurak\Iris\Color\Hex;
use Symfony\Component\Stopwatch\Stopwatch;

class ClassController extends AbstractController implements AuthenticatedInterface
{

    /**
     * Show events for a class and a user given.
     *
     * @Route("/classes/{id}", name="class")
     * @param Request $request
     * @param String $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     */
    public function class(Request $request, String $id = ''): Response
    {
        $date = $request->get('date');

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
                return $this->render('User/Professor/class.twig', ['class' => $class]);
            }

            if ($class->sourcedId === $enrollment->class->sourcedId) { // Check if the user is enrolled to this class

                if ($enrollment->role === 'student') {
                    return $this->studentClass($class, $date);
                }

                // Else user is teacher of this class
                return $this->render('User/Professor/class.twig', ['class' => $class]);
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
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     */
    private function studentClass(Klass $class, $date)
    {
        $userId = self::loggedUser();
        $classId = $class->sourcedId;
        $indicators = [];
        $tempWeight = [];
        $weight = [];

        try {
            if ($date === null) {
                $risk = Risk::latestByClassAndUser($classId, $userId);
                $date = date('Y-m-d', strtotime($risk->dateTime));
            } else {
                $risk = Risk::findByClassAndUserAndDate($classId, $userId, $date);
                $date = date('Y-m-d', strtotime($date));
            }
        } catch (Exception $e) {
            return $this->render('User/Student/class.twig', [
                'date' => $date,
                'class' => $class,
                'userWeekScores' => null
            ]);
        }

        // - - - Day score - - -
        foreach ($risk->metadata as $attribute => $value) {
            if (strpos($attribute, 'global') !== 0 ) {
                if (stripos($attribute, 'Weight')) {
                    $tempWeight[$attribute] = (float)$value;
                }else{
                    if (strpos($value, '/')) {
                        $explode = explode('/', $value);
                        if ((int)$explode[1] === 0) {
                            continue;
                        }
                        $value = round(($explode[0] / $explode[1]) * 100, 1);
                    }else{
                        $value = round($value * 100, 1);
                    }
                    $indicators[$attribute] = $value;
                }
            }
        }

        // Calculate the radian factor
        $res = (2 * M_PI) / array_sum($tempWeight);
        foreach ($tempWeight as $key => $w) {
            $weight[$key] = $w * $res;
        }


        $legend = $this->indicatorsLegend($indicators, $weight);
        $userWeekScores = array_filter($this->userWeekScores($date, $risk, $userId)); // remove index when no risk
        $classWeekScores = $this->classWeekScores($class, $date);

        return $this->render('User/Student/class.twig', [
            'date' => $date,
            'class' => $class,
            'legend' => $legend,
            'indicators' => $indicators,
            'userWeekScores' => $userWeekScores,
            'classWeekScores' => $classWeekScores
        ]);
    }


    /**
     * Return the score of the whole class for the last seven weeks
     *
     * @param Klass $class
     * @param $date
     * @return array
     */
    private function classWeekScores(Klass $class, $date)
    {

        $classId = $class->sourcedId;
        $scores = [];
        for ($i = 0; $i < 8; $i++) {
            $day = date('Y-m-d', strtotime("-$i week", strtotime($date)));
            try {
                $risks = Risk::findByClassAndDate($classId, $day);
                foreach ($risks as $key => $risk) {
                    foreach ($risk->metadata as $attribute => $value) {
                        if (strpos($attribute, 'global') === 0 ) {
                            $trimmed = preg_replace('/\D/', '', $attribute); // get int value
                            $arr = explode('/', $value, 2); // cut the string in two parts
                            $value = $arr[0]; // take the first part of the string
                            if ($key === array_key_first($risks)) { // init first iteration
                                $scores[$day][$trimmed] = $value / count($risks);
                            } else {
                                $scores[$day][$trimmed] += $value / count($risks);
                            }
                            $scores[$day][$trimmed] = round($scores[$day][$trimmed],1);
                        }
                    }
                }
            } catch (Exception $e) {
            }
        }

        return $scores;
    }


    /**
     * Generate the legend for the pie chart
     *
     * @param $indicators
     * @param $weight
     * @return array
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     */
    private function indicatorsLegend($indicators, $weight)
    {
        $dictionary = $this->dictionary();
        $categories = $dictionary['class']['tab_one']['chart_one']['legend'];
        $colors = $this->primaryMaterialColors();

        $legend = [];

        foreach ($categories as $category) {
            $showCategory = false;
            $categoryName = $category['name'];
            $legend[$categoryName]['indicators'] = [];
            $primaryColor = $colors[array_rand($colors)];
            $colors = array_diff($colors, [$primaryColor]); // remove the color since it has been picked
            
            $i = 0;
            foreach ($indicators as $key => $value) {
                if (array_key_exists($key, $category['labels'])) {

                    $showCategory = true;
                    $velocity = $weight[$key . 'Weight'];

                    $value = [
                        'value' => $value,
                        'velocity' => $velocity,
                        'color' => $this->shadeOf($primaryColor, $i),
                        'name' =>  $category['labels'][$key]
                    ];
                    $legend[$categoryName]['indicators'] += [$key => $value];
                    unset($indicators[$key]);
                    $i++;
                }
            }

            if ($showCategory) {
                $legend[$categoryName]['name'] = $categoryName;
            } else {
                unset($legend[$categoryName]); // remove the category since it's empty
            }

        }

        // Unknown indicators
        if (count($indicators)> 0){
            $categoryName = 'Other';
            $legend[$categoryName]['indicators'] = [];
            $legend[$categoryName]['name'] = $categoryName;

            foreach ($indicators as $key => $value) {
                $value = [
                    'value' => $value,
                    'velocity' => $weight[$key . 'Weight'],
                    'color' => $colors[array_rand($colors)],
                    'name' =>  $key
                ];

                $legend[$categoryName]['indicators'] += [$key => $value];
            }
        }

        return $legend;
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
     * Get the scores of a user for the 7 last weeks
     *
     * @param $initDate
     * @param $firstRisk
     * @param $userId
     * @return array
     */
    private function userWeekScores(string $initDate, Risk $firstRisk, string $userId): array
    {
        $weekScores[$initDate] = $this->extractScoresFromRisk($firstRisk);

        for ($i = 1; $i < 7; $i++) {
            $day = date('Y-m-d', strtotime("-$i week", strtotime($initDate)));

            try {
                $risk = Risk::findByClassAndUserAndDate($firstRisk->classSourcedId, $userId, $day);
                $weekScore = $this->extractScoresFromRisk($risk);
            } catch (NotFoundException $e) {
                $dayScores = null;
            }

            $weekScores[$day] = $weekScore;
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
            $scores[$trimmed] = (int) $value; // already a percentage
        }

        return $scores;
    }

    /**
     * Return primary colors from Material scheme (level 700)
     */
    private function primaryMaterialColors()
    {
        return ['#d32f2f', '#ad1457', '#1976d2', '#388e3c', '#e64a19', '#5d4037', '#616161' ];
    }

    /**
     * Return a shade of given color (hex string)
     *
     * @param Hex $color
     * @param int $coefficient
     * @return string
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     */
    private function shadeOf($color, $coefficient = 1)
    {
        $hex = new Hex($color);

        if ($hex->isDark()) {
            $hex = $hex->lighten($coefficient * 10 + 2);
            $color = $hex->spin(5 * $coefficient);
        }else {
            $hex = $hex->darken($coefficient * 10 + 2);
            $color = $hex->spin(5 * $coefficient);
        }

        return (string) $color;
    }


}