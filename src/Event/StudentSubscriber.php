<?php

namespace App\Event;

use App\Controller\AuthenticatedInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;

class StudentSubscriber implements EventSubscriberInterface
{


    public function __construct(ContainerInterface $container)
    {

    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof AuthenticatedInterface) {
            if (isset($_SESSION['phpCAS']['user'])) {
                throw new AccessDeniedHttpException('Access forbidden.');
            }
        }
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType())
            return;
    }


    public static function getSubscribedEvents()
    {
        return [ KernelEvents::CONTROLLER => 'onKernelController' ];
    }
}