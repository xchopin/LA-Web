<?php

namespace App\Model;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class User extends Eloquent {

    protected $collection = 'mongoUser';

    protected $primaryKey = 'id';

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


