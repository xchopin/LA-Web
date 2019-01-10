<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;


class Klass extends ApiModel
{

    public static function find(String $id)
    {
        return parent::get("classes/$id");
    }

    public static function enrollments(String $id)
    {
        return parent::get("classes/$id/enrollments");
    }

    public static function events(String $id)
    {
        return parent::get("classes/$id/events/stats");
    }

    public static function eventsForUser(String $id, String $userId)
    {
        return parent::get("classes/$id/events/user/$userId");
    }

    public static function resultsForUser(String $id, String $userId)
    {
        return parent::get("classes/$id/results/user/$userId");
    }

    public static function lineItems(String $id) {
        return parent::get("classes/$id/lineitems");
    }

}