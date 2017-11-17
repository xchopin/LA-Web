<?php

namespace AuthBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AuthBundle\Controller\AdminAuthenticatedController;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class AdminSubscriber implements EventSubscriberInterface
{
    private $administrators;

    public function __construct(ContainerInterface $container)
    {
        $this->administrators = $container->getParameter('administrators');
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

        if ($controller[0] instanceof AdminAuthenticatedController) {
            $isAdmin = false;
            if (isset($_SESSION['phpCAS']['user'])) {
                if (in_array($_SESSION['phpCAS']['user'], $this->administrators))
                    $isAdmin = true;
            }
            if (!$isAdmin) throw new AccessDeniedHttpException('Access forbidden.');
        }
    }

    public static function getSubscribedEvents()
    {
        return [ KernelEvents::CONTROLLER => 'onKernelController' ];
    }
}