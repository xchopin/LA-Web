<?php

namespace App\Event;

use App\Controller\AuthenticatedInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\SessionUnavailableException;

class AuthenticatedSubscriber implements EventSubscriberInterface
{

    private $router;

    public function __construct(ContainerInterface $container, RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onKernelController(FilterControllerEvent $event): void
    {
        $controller = $event->getController();
        $session = $event->getRequest()->getSession();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller))
            return;

        if ($controller[0] instanceof AuthenticatedInterface) {

            $url = $this->router->generate('login', ['redirect' => $event->getRequest()->getPathInfo()]);
            if (isset($_SESSION['phpCAS']['user']) === false) {
                $event->setController(static function() use ($url) {
                    return new RedirectResponse($url);
                });
            } else if ($session->get('rulesAgreement') === false) {
                $url = $this->router->generate('rules-agreement');
                $event->setController(static function() use ($url) {
                    return new RedirectResponse($url);
                });
            }

        }
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (HttpKernel::MASTER_REQUEST !== $event->getRequestType())
            return;
    }


    public static function getSubscribedEvents()
    {
        return [ KernelEvents::CONTROLLER => 'onKernelController' ];
    }
}