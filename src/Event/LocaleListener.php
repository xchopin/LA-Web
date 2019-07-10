<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Event;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;


class LocaleListener
{

    /**
     * {@inheritdoc}
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (HttpKernel::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }
    }


    public static function getSubscribedEvents(): array
    {
        return [ KernelEvents::REQUEST => [['onKernelRequest', 200]] ];
    }
}