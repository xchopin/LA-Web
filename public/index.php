<?php

CONST DEBUG_MODE = TRUE;
CONST SETTINGS_PROD = __DIR__ . '/../app/settings.php';
CONST SETTINGS_DEV = __DIR__ . '/../app/settings_dev.php';

session_start();

require __DIR__ . '/../vendor/autoload.php';

$app = new Slim\App(DEBUG_MODE ? require SETTINGS_DEV : require SETTINGS_PROD);

require __DIR__ . '/../app/database.php';

require __DIR__ . '/../app/dependencies.php';

require __DIR__ . '/../app/handlers.php';

require __DIR__ . '/../app/middleware.php';

require __DIR__ . '/../app/controllers.php';

require __DIR__ . '/../app/routes.php';

$app->run();
