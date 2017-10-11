<?php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager;

$container = $app->getContainer();
$capsule = new Manager();
$capsule->getDatabaseManager()->extend('mongodb', function($config)
{
    return new Jenssegers\Mongodb\Connection($config);
});

$capsule->addConnection([
    'driver' => 'mongodb',
    'host' => 'jay.dc.univ-lorraine.fr',
    'port' => '27017',
    'database' => 'test-matthews',
    'username' => 'test-matthews',
    'password' => 'm2pTM4tth3ws',
    'options'  => [
        'database' => 'test-matthews' // sets the authentication database required by mongo 3
    ]
],
    'default'
);

$capsule->bootEloquent();
$capsule->setAsGlobal();
$container['db'] = function () use ($capsule) {
    return $capsule;
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
            return '5.4';
        }
    };
}