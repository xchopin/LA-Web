<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;


class User extends ApiModel
{

    public static function find(String $id)
    {
        return parent::get("users/$id");
    }

    public static function enrollments(String $id)
    {
        return parent::get("users/$id/enrollments");
    }

    public static function events(String $id)
    {
        return parent::get("users/$id/events");
    }

    public static function eventsFrom(String $id, String $from)
    {
        return parent::get("users/$id/events?from=$from");
    }

    public static function eventsFromTo(String $id, String $from, String $to)
    {
        return parent::get("users/$id/events?from=$from&to=$to");
    }


}