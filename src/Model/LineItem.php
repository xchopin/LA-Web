<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;


class LineItem extends ApiModel
{

    public static function find(String $id)
    {
        return parent::get("classes/$id"); # does not exist yer
    }


}