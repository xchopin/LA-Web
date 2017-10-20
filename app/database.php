<?php

use Symfony\Component\Yaml\Yaml;
use Illuminate\Database\Capsule\Manager;

$parameters = Yaml::parse(file_get_contents(__DIR__ . '/config/parameters.yml'));

$capsule = new Manager();

$capsule->getDatabaseManager()->extend('mongodb', function($config)
{
    return new Jenssegers\Mongodb\Connection($config);
});

$capsule->addConnection($parameters['nosql']);

$capsule->bootEloquent();

$capsule->setAsGlobal();

$container = $app->getContainer();

$container['db'] = function () use ($capsule) {
    return $capsule;
};

$container['parameters'] = function () use ($parameters) {
    return $parameters;
};

$ldapInstance = ldap_connect($parameters['ldap']['host'], $parameters['ldap']['port']);

ldap_set_option($ldapInstance, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapInstance, LDAP_OPT_REFERRALS, 0);
ldap_set_option($ldapInstance, LDAP_OPT_SIZELIMIT, 5000);

$container['ldap'] = function () use ($ldapInstance) {
    return $ldapInstance;
};


/**
 * Fix the Jenssegers/MongoDB dependency issue for Query Builders when not using Lumen router or Laravel Framework
 * (It helps to know by using the version if it uses tables or collections)
 * @return String, Eloquent ORM version
 */
function app()
{
    return new class
    {
        public function version()
        {
            return '5.5';
        }
    };
}