<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Admin\Controller;

use App\Model\User;
use Respect\Validation\Validator as V;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Controller\Controller;

class AdminController extends Controller
{

    public function findStudent(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $ldapInstance = $this->container['ldap'];

            $baseDN = $this->container['parameters']['ldap']['base_dn'];
            $keyword = $request->getParam('name');

            $filter = '(&(businesscategory=E*)(displayname=*' . $keyword . '*))';

            $query = ldap_search ($ldapInstance, $baseDN, $filter, ['displayname', 'uid']);
            $students = ldap_get_entries($ldapInstance, $query);

            $res = [];
            foreach($students as $student) {
                if ($student['uid'][0] != null)
                    $res += [ User::moodleId($student['uid'][0]) => $student['displayname'][0] ];
            }

            return $this->json($response, $res);
        }

        return $this->view->render($response, 'find-student.twig');
    }
}
