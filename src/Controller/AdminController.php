<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;


use OpenLRW\Exception\NotFoundException;
use OpenLRW\Model\Klass;
use OpenLRW\Model\OneRoster;
use OpenLRW\Model\User;
use OpenLRW\OpenLRW;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\JsonResponse;
/**
 * @Route("/tools")
 */
class AdminController extends AbstractController implements AdminAuthenticatedInterface
{
    /**
     * GET: Renders a form page to find a person
     * POST: Returns JSON data for a name given
     *
     * @Route("/view-as", name="view-as")
     * @param Request $request
     * @return JsonResponse | Response
     */
    public function viewAsPage(Request $request)
    {
        if ($request->isMethod('POST')) {
            $keyword = $request->get('name');
            $filter = $request->get('filter_by');
            $response = $this->searchStudentLdap($keyword, $filter);

            return $this->json($response);
        }

        return $this->render('Admin/view-as.twig');
    }


    /**
     * @Route("/check/users/{userId}", name="check-user", defaults={"userId": "null"})
     * @param Request $request
     * @param string userId
     * @return Response
     */
    public function checkUserPage(Request $request, string $userId)
    {

        if ($userId !== 'null') {
            $username =  $userId;

            try {
                $user = OneRoster::httpGet("users/$username");
                $enrollments = User::enrollments($username);
            }catch (NotFoundException $e) {
                return $this->render('Admin/check-user.twig', ['not_found' => true]);
            }

            $classes = [];
            $userResults= [];
            foreach ($enrollments as $enrollment) {
                $classTitle = 'Class does not exist.';
                $classId = $enrollment->class->sourcedId;
                try {
                    $class = Klass::find($classId);
                    $classTitle = $class->title;

                    $results = Klass::resultsForUser($classId, $username);

                    $userResults[$classId] = [];
                    foreach ($results as $result) {
                        $type = 'Undefined';
                        $date = '';
                        if (property_exists($result->metadata, 'type')) {
                            $type = $result->metadata->type;
                        }

                        if (property_exists($result, 'date')) {
                            $date = $result->date;
                        }
                        $add = [
                            'score' => $result->score,
                            'source' => $result->metadata->category,
                            'type' => $type,
                            'classTitle' => $classTitle,
                            'date' => $date,
                        ];
                        $userResults[$classId][] = $add;

                    }

                }catch (NotFoundException $e) {
                    // nothing
                }


                if (! isset($classes[$enrollment->role])) {
                    $classes[$enrollment->role] = []; // init
                }
                $classes[$enrollment->role][] = ['id' => $classId, 'title' => $classTitle];



            }


            return $this->render('Admin/check-user.twig', [
                'result' => [
                    'user' => json_decode(json_encode($user), true),
                    'enrollments' => $classes,
                    'results' => $userResults
                ]
            ]);
        }
        return $this->render('Admin/check-user.twig');
    }


    /**
     *
     * @Route("/check/classes/{classId}", name="check-class", defaults={"classId": "null"})
     * @param Request $request
     * @param string $classId
     * @return Response
     */
    public function checkClassPage(Request $request, string $classId){
        if ($classId === 'null') {
            return $this->render('Admin/check-class.twig');
        }else{
            try {
                $class = OneRoster::httpGet("classes/$classId");

            }catch (NotFoundException $e) {
                return $this->render('Admin/check-class.twig', ['not_found' => true]);
            }

            $enrollments = null;
            $classEnrollments = [];
            try {
               $enrollments = Klass::enrollments($classId);
                foreach ($enrollments as $enrollment) {
                    $classEnrollments[$enrollment->role][] = $enrollment->user->sourcedId;
                }

            }catch (NotFoundException $e){
                // nothing
            }


            return $this->render('Admin/check-class.twig', [
                'result' => [
                    'class' => json_decode(json_encode($class), true),
                    'enrollments' => $classEnrollments
                ]
            ]);
        }
    }

    /**
     * Search a name in the LDAP database
     *
     * @param $keyword
     * @param $filter
     * @return array
     */
    protected function searchStudentLdap($keyword, $filterBy): array
    {
        if ($filterBy === 'login') {
            $filter = '(&(uid=*' . $keyword . '*))';
        } else {
            $filter = '(&(displayname=*' . $keyword . '*))';
        }

        $students = $this->ldap($filter, ['displayname', 'uid']);
        $res = [];

        foreach($students as $student) {
            if ($student['uid'][0] !== null) {
                $res += [$student['uid'][0] => $student['displayname'][0]];
            }
        }

        return $res;
    }

    /**
     * Check if the user is in the mode "View as"
     *
     * @return bool
     */
    public static function isInViewAs() : bool
    {
       return isset($_SESSION['originalUsername']);
    }

    /**
     * Clean the SESSION variables that are related to admin modes
     */
    private function cleanAdminModes()
    {
        unset($_SESSION['originalUsername'], $_SESSION['professorMode']);
    }

    /**
     *
     * @Route("/view/{id}", name="enable-view")
     * @param Request $request
     * @param String $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enableViewAs(Request $request, String $id)
    {

        $_SESSION['originalUsername'] = $_SESSION['phpCAS']['user'];
        $_SESSION['phpCAS']['user'] = $id;

        $result = $this->ldapFirst("uid=$id");
        $_SESSION['name'] = $result['displayname'][0];


        return $this->redirectToRoute('home');
    }


    /**
     * Restore the original user
     */
    private function revertOriginalUser()
    {
        $username = $_SESSION['originalUsername'];
        $result = $this->ldapFirst("uid=$username");
        $_SESSION['phpCAS']['user'] = $username;
        $_SESSION['name'] = $result['displayname'][0];
    }


    /**
     * Leave the "View as" mode.
     *
     * @Route("/view/actions/leave", name="leave-view-as")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function disableViewAs(Request $request)
    {
        $this->revertOriginalUser();
        $this->cleanAdminModes(); // Remove unused variables
        return $this->redirectToRoute('home');
    }

    /**
     * Redirect to "View as" form page by giving back admin rights.
     *
     * @Route("/view/actions/new", name="new-view-as")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeTarget(Request $request)
    {
        $this->revertOriginalUser();
        $this->cleanAdminModes();
        return $this->redirectToRoute('view-as');
    }

    /**
     * Enable the "Professor Mode"
     * Allow to see every classes as a professor
     *
     * @Route("/professor-view", name="professor-view")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enableProfessorMode(Request $request): Response
    {
        if (self::isInViewAs()) {
            $this->revertOriginalUser();
            $this->cleanAdminModes(); // One mode at a time
        }

        $_SESSION['professorMode'] = true;
        $route = $request->headers->get('referer'); // Previous page

        return $this->redirect($route);
    }

    /**
     * Disable the "Professor Mode"
     *
     * @Route("/professor-view/actions/leave", name="leave-professor-view")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function disableProfessorMode(Request $request): Response
    {
        $this->cleanAdminModes();
        return $this->redirectToRoute('home');
    }


    /**
     * Enable or Disable a class
     *
     * @Route("/class-management", name="class-management")
     * @param Request $request
     * @return JsonResponse | Response
     */
    public function classManagement(Request $request)
    {

        //if ($request->isMethod('POST')) {
            $keyword = $request->get('title');
            $classes = Klass::all();
            $res = [];

            foreach($classes as $class) {
                $this->debug($classes);

                if ($class->title === $keyword) {
                    $res += [$class->sourcedId => $class->title];
                }
            }

            return $this->json($res);
        //}

        //return $this->render('Admin/view-as.twig');
    }
}

