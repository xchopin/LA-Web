<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Model\API\User;
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

        //ToDo: separate with ajax calls
        $classes = [];

        $user = User::find($id);

        if ($user === null) {
            $this->addFlash('error', "Student `$id` does not exist");
            return $this->redirectToRoute('home');
        }

        $events = User::events($id);

        $activities = [];

        if ($events != null)
        {
            foreach ($events as $event)
                array_push($activities, $event->object->{'@type'});

            $activities = array_count_values($activities);
        }

       //foreach (json_decode($enrollments->getBody()->getContents()) as $enrollment) {
       //    if ($enrollment->class->title != null)
       //        array_push($classes, $enrollment->class);
       //}

        return $this->render('Admin/user.twig', [
            'givenName' => $user->givenName,
            'classes' => null,
            'events' => $events,
            'event_activities' => json_encode($activities, JSON_UNESCAPED_SLASHES )
        ]);

    }

}

