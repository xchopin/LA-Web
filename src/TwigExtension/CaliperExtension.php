<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\TwigExtension;

use Twig_Extension;
use Twig_SimpleFunction;
use App\Model\Caliper;

class CaliperExtension extends Twig_Extension
{

    public function getName()
    {
        return 'caliper_extension';
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('view_event', [$this, 'visitPage'])
        ];
    }

    /**
     * Send a Caliper event to the OpenLRW when a student visits a web page.
     *
     * @param string $uri
     * @return bool
     */
    public function visitPage($uri = "")
    {
        if (isset($_SESSION['phpCAS']['user'])) {
             //if ( $_SESSION['isAdmin'] || isset($_SESSION['username']))
             //   return false;  // Check if it's an admin (even in as view mode)

            $userId = $_SESSION['phpCAS']['user'];
            Caliper::create($userId, "Viewed", "Viewed page $uri", $uri);
        }
    }
}
