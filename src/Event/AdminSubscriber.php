<?php

namespace App\Event;

use App\Controller\AdminAuthenticatedInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;

class AdminSubscriber implements EventSubscriberInterface
{
    private $administrators;

    public function __construct(ContainerInterface $container)
    {
        $this->administrators = explode(',', env('ADMINISTRATORS'));
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

        if ($controller[0] instanceof AdminAuthenticatedInterface) {
            $isAdmin = false;
            if (isset($_SESSION['phpCAS']['user'])) {
                if (in_array($_SESSION['phpCAS']['user'], $this->administrators))
                    $isAdmin = true;
            }
            if (!$isAdmin) throw new AccessDeniedHttpException('Access forbidden.');
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