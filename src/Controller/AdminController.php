<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;


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
    public function findPerson(Request $request)
    {
        if ($request->isMethod('POST')) {
            $keyword = $request->get('name');
            $filter = '(&(displayname=*' . $keyword . '*))';
            $students = $this->ldap($filter, ['displayname', 'uid']);
            $res = [];

            foreach($students as $student) {
                if ($student['uid'][0] !== null)
                    $res += [ $student['uid'][0] => $student['displayname'][0] ];
            }

            return $this->json($res);
        }

        return $this->render('Admin/find-student.twig');
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
        $_SESSION['email'] = $result['mail'][0];



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
        $_SESSION['email'] = $result['mail'][0];
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
}

