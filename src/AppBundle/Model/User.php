<?php

namespace AppBundle\Model;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class User extends Eloquent {

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
        $user = User::find($uid);
        return $user == null ? null : $user->user['sourcedId'];
    }

}


