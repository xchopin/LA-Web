<?php

namespace App\Event;

use App\Controller\AdminAuthenticatedInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class AdminSubscriber implements EventSubscriberInterface
{
    private $mode;
    private $administrators;
    private $container;
    private $ldap;
    private $baseDN;
    const PREFIX = 'ADMIN_';
    const GROUP_MODE = 'GROUP';
    const USERS_MODE = 'USERS';

    public function __construct(ContainerInterface $container)
    {
        $this->ldap = ldap_connect(env('LDAP_HOST'), env('LDAP_PORT'));
        $this->baseDN = env('LDAP_BASE_DN');
        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
        $container->set('ldap', /** @scrutinizer ignore-type */ $this->ldap);
        $container->set('ldap_basedn', /** @scrutinizer ignore-type */ $this->baseDN);

        $this->container = $container;
        $this->mode = env('ADMIN_MODE');

        if ($this->mode === self::GROUP_MODE)
            $this->administrators = env(self::PREFIX . self::GROUP_MODE);
        else if ($this->mode === self::USERS_MODE)
            $this->administrators = explode(',', env(self::PREFIX . self::USERS_MODE));
        else
            throw new InvalidParameterException("Administators mode has an undefined value, please refer to the documentation.");
    }

    /**
     * Protect controllers from non administrator users
     *
     * @param FilterControllerEvent $event
     */
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
            if ($this->isAdmin() === false ) throw new AccessDeniedHttpException('Access forbidden.');
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

    /**
     * Check if the logged user has the admin role
     *
     * @return bool
     */
    public function isAdmin()
    {
        $isAdmin = false;
        if (isset($_SESSION['phpCAS']['user'])) {
            $userId = $_SESSION['phpCAS']['user'];
            if ($this->mode === self::USERS_MODE) {
                if (in_array($userId, $this->administrators))
                    $isAdmin = true;
            } else {
                $result = ldap_get_entries(
                    $this->ldap,
                    ldap_search($this->ldap, $this->baseDN, "(&(udlGroup=$this->administrators)(uid=$userId))")
                );
                $result['count'] > 0 ? $isAdmin = true : false;
            }
        }
        $_SESSION['isAdmin'] = $isAdmin;
        return $isAdmin;
    }
}