<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class MongoUser extends Eloquent {

    protected $collection = 'mongoUser';

    protected $primaryKey = 'user.userId';

    /**
     * Returns the Moodle id for a LDAP uid given.
     *
     * @param $uid
     * @return mixed
     */
    public static function moodleId($uid)
    {
        $user = MongoUser::find($uid);
        return $user == null ? null : $user->user['sourcedId'];
    }


}


