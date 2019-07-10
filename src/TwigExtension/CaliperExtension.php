<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\TwigExtension;

use OpenLRW\Model\Event;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\Session\Session;

class CaliperExtension extends AbstractExtension
{

    public function getName()
    {
        return 'caliper_extension';
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('view_event', [$this, 'visitPage'])
        ];
    }

    /**
     * Send a Caliper event to the OpenLRW when a student visits a web page.
     *
     * @param string $uri
     * @return bool
     */
    public function visitPage($uri = '')
    {
        if (isset($_SESSION['phpCAS']['user'])) {
             //if ( $_SESSION['isAdmin'] || isset($_SESSION['username']))
             //   return false;  // Check if it's an admin (even in as view mode)

            $userId = $_SESSION['phpCAS']['user'];
            Event::caliperFactory($userId, 'Viewed', '', $uri);
        }
    }
}
