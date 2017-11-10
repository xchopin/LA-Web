<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\EventListener;


use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;


class LocaleListener
{

    private $defaultLocale;
    private $acceptedLocales;
    private $router;

    public function __construct(RouterInterface $router, $defaultLocale = '', $locales = [])
    {
        $this->router = $router;
        $this->defaultLocale = $defaultLocale;
        $this->acceptedLocales = $locales;
    }

    /**
     *  Aims to redirect the client on its preferred language if available
     *  (First connection : browser language then check his favorite language in the 'lang' cookie)
     *
     * @Observe("kernel.request")
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
      /**  $request = $event->getRequest();
        $route = $request->get('_route');
       *
        $languageId = $request->cookies->has('lang') ? $request->cookies->get('lang') : substr($request->getPreferredLanguage(), 0, 2);
        if (!in_array($languageId, $this->acceptedLocales))
            $languageId = $this->defaultLocale;

        $url = $this->router->generate($route, ['_locale' => $languageId] + $request->attributes->get('_route_params'));
        $response = new RedirectResponse($url);
        $response->headers->setCookie(new Cookie('lang', $languageId, time() + 3600 * 24 * 7));

        $event->setResponse($response);*/
    }

    public static function getSubscribedEvents()
    {
        return [ KernelEvents::REQUEST => [['onKernelRequest', 200]] ];
    }
}