<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Event;

use Jenssegers\Mongodb\Connection;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Illuminate\Database\Capsule\Manager;


class LocaleListener
{
    private $container;

    public function __construct(ContainerInterface $container)
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

        $this->registerCapsule();

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
        $capsule->addConnection([
            'driver' => 'mongodb',
            'host' => env('DB_HOST'),
            'port' => intval(env('DB_PORT')),
            'database' => env('DB_NAME'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'options' => [
                'database' => env('DB_NAME'),
            ]
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $this->container->set('capsule', $capsule);
    }

    public static function getSubscribedEvents()
    {
        return [ KernelEvents::REQUEST => [['onKernelRequest', 200]] ];
    }
}