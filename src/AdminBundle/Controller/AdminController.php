<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AdminBundle\Controller;

use AppBundle\Model\User;
use AuthBundle\Controller\AdminAuthenticatedController;
use CoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/tools")
 */
class AdminController extends AbstractController implements AdminAuthenticatedController
{
    /**
     * GET : Renders a form page to find a student
     * POST : Returns JSON data for a name given
     *
     * @Route("/find-student", name="find-student")
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
     * Renders the profile of a student with several data from OpenLRW API.
     *
     * @Route("/student-profile/{id}", name="student-profile")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function studentProfile(Request $request, $id = '')
    {
        $classes = [];
        $moodleId = User::moodleId($id);

        if ($moodleId === null) {
            $this->addFlash('error', "Student `$id` does not exist");
            return $this->redirectToRoute('home');
        }

        $enrollments = $this->http->get('users/' . User::moodleId($id) . '/enrollments', [
            'headers' => ['Authorization' => 'Bearer ' . $this->createJWT()->token]
        ]);

        foreach (json_decode($enrollments->getBody()->getContents()) as $enrollment) {
            if ($enrollment->class->title != null)
                array_push($classes, $enrollment->class);
        }

        return $this->render('admin/user.twig',[
            'classes' => $classes,
            'student_name' => $this->ldapFirst("uid=$id")['displayname'][0]
        ]);

    }

}

