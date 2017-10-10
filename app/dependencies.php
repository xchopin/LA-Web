<?php

use App\TwigExtension\AssetExtension;
use Awurth\SlimValidation\Validator;
use Awurth\SlimValidation\ValidatorExtension;
use Illuminate\Database\Capsule\Manager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
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

// Fix Jenssegers/MongoDB issues with Query Builders when not using Lumen or Laravel framework
function app(){return new class{public function version(){return '5.4';}};}

$capsule->bootEloquent();
$capsule->setAsGlobal();
$container['db'] = function () use ($capsule) {
   return $capsule;
};

$container['flash'] = function () {
    return new Messages();
};

$container['validator'] = function () {
    return new Validator();
};

$container['view'] = function ($container) {
    $settings = $container['settings'];

    $view = new Twig(
        $settings['view']['templates_path'],
        $settings['view']['twig']
    );

    $view->addExtension(new TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));
    $view->addExtension(new Twig_Extension_Debug());
    $view->addExtension(new AssetExtension(
        $container['request'],
        isset($settings['assets']['base_path']) ? $settings['assets']['base_path'] : ''
    ));
    $view->addExtension(new ValidatorExtension($container['validator']));

    $view->getEnvironment()->addGlobal('flash', $container['flash']);
  //  $view->getEnvironment()->addGlobal('auth', $container['auth']);

    return $view;
};

$container['monolog'] = function ($container) {
    $settings = $container['settings']['monolog'];

    $logger = new Logger($settings['name']);
    $logger->pushProcessor(new UidProcessor());
    $logger->pushHandler(new StreamHandler($settings['path'], Logger::DEBUG));

    return $logger;
};
