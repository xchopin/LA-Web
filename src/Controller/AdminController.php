<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Model\User;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/tools")
 */
class AdminController extends AbstractController implements AdminAuthenticatedInterface
{
    /**
     * GET: Renders a form page to find a student
     * POST: Returns JSON data for a name given
     *
     * @Route("/view-as", name="view-as")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse | \Symfony\Component\HttpFoundation\Response
     */
    public function findStudent(Request $request)
    {
        if ($request->isMethod('POST')) {
            $keyword = $request->get('name');
            $filter = '(&(businesscategory=E*)(displayname=*' . $keyword . '*))';
            $students = $this->ldap($filter, ['displayname', 'uid']);
            $res = [];

            foreach($students as $student) {
                if ($student['uid'][0] != null)
                    $res += [ $student['uid'][0] => $student['displayname'][0] ];
            }

            return $this->json($res);
        }

        return $this->render('Admin/find-student.twig');
    }

    /**
     *
     * @Route("/view/{id}", name="enable-view")
     * @param Request $request
     * @param String $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAs(Request $request, String $id)
    {
        $_SESSION['username'] = $_SESSION['phpCAS']['user'];
        $_SESSION['phpCAS']['user'] = $id;

        $result = $this->ldapFirst("uid=$id");
        $_SESSION['name'] = $result['displayname'][0];
        $_SESSION['email'] = $result['mail'][0];

        return $this->redirectToRoute('home');
    }
}

