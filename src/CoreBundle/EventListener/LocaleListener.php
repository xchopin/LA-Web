<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoreBundle\EventListener;

use Jenssegers\Mongodb\Connection;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Illuminate\Database\Capsule\Manager;


class LocaleListener
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType())
            return;

        $this->registerLDAP();
        $this->registerCapsule();
    }

    /**
     * Registers a LDAP Instance into the container.
     */
    private function registerLDAP()
    {
        $settings = $this->container->getParameter('ldap');
        $ldapInstance = ldap_connect($settings['host'], $settings['port']);
        ldap_set_option($ldapInstance, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapInstance, LDAP_OPT_REFERRALS, 0);
        $this->container->set('ldap', /** @scrutinizer ignore-type */ $ldapInstance);
    }

    /**
     * Sets globally and registers the Eloquent ORM's Capsule Manager into the container.
     */
    private function registerCapsule()
    {
        $capsule = new Manager();
        $capsule->getDatabaseManager()->extend('mongodb', function($config) {
            return new Connection($config);
        });
        $capsule->addConnection($this->container->getParameter('nosql'));
        $capsule->bootEloquent();
        $capsule->setAsGlobal();
        $this->container->set('capsule', $capsule);
    }

    public static function getSubscribedEvents()
    {
        return [ KernelEvents::REQUEST => [['onKernelRequest', 200]] ];
    }
}