<?php

namespace App\Model;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class User extends Eloquent {

    protected $collection = 'mongoUser';

    protected $primaryKey = 'user.userId';

    public static function moodleId($uid)
    {
        return User::find($uid)->user['sourcedId'];
    }

   /** protected $fillable = [
        'username',
        'email',
        'password',
        'last_name',
        'first_name',
        'permissions',
    ];*/

    //protected $loginNames = ['username', 'email'];
}


