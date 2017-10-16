<?php

use App\Resources\TwigExtension\AssetExtension;
use Awurth\SlimValidation\Validator;
use Awurth\SlimValidation\ValidatorExtension;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

define('DICTIONARY_PATH', 'Resources/Translation/');

$container = $app->getContainer();

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

    $view->addExtension(new AssetExtension(
        $container['request'],
        isset($settings['assets']['base_path']) ? $settings['assets']['base_path'] : ''
    ));

    $view->addExtension(new Twig_Extension_Debug());
    $view->addExtension(new Security\Resources\TwigExtension\AuthExtension());
    $view->addExtension(new ValidatorExtension($container['validator']));

    $view->getEnvironment()->addGlobal('flash', $container['flash']);


    return $view;
};

$container['monolog'] = function ($container) {
    $settings = $container['settings']['monolog'];

    $logger = new Logger($settings['name']);
    $logger->pushProcessor(new UidProcessor());
    $logger->pushHandler(new StreamHandler($settings['path'], Logger::DEBUG));

    return $logger;
};
