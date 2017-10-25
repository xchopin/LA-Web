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
        $token = $this->createJWT()->token;
        $id = User::moodleId($id);

        $enrollments = $this->http->get("users/$id/enrollments",  ['headers' => ['Authorization' => "Bearer $token"]]);
        $user = $this->http->get("users/$id",  ['headers' => ['Authorization' => "Bearer $token"]]);

        $userId = json_decode($user->getBody()->getContents())->userId;

        foreach (json_decode($enrollments->getBody()->getContents()) as $enrollment) {
            if ($enrollment->class->title != null)
                array_push($classes, $enrollment->class);
        }

        return $this->view->render($response, 'App/user.twig',[
            'classes' => $classes,
            'student_name' => $this->ldap("uid=$userId")['displayname'][0]
        ] );

    }

}