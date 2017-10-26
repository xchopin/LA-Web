<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;


use Slim\Http\Request;
use Slim\Http\Response;
use App\Model\User;

class UserController extends Controller
{

    public function user(Request $request, Response $response, $country, $id)
    {

        $classes = [];

        $enrollments = $this->http->get('users/' . User::moodleId($id) . '/enrollments', [
            'headers' => ['Authorization' => 'Bearer ' . $this->createJWT()->token]
        ]);

        foreach (json_decode($enrollments->getBody()->getContents()) as $enrollment) {
            if ($enrollment->class->title != null)
                array_push($classes, $enrollment->class);
        }

        return $this->view->render($response, 'App/user.twig',[
            'classes' => $classes,
            'student_name' => $this->ldapFirst("uid=$id")['displayname'][0]
        ] );

    }

}