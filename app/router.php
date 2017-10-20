<?php

const BUNDLE_DIR = __DIR__ . '/../src/';
const ROUTE_PATH = '/Resources/routes.php';

require_once(BUNDLE_DIR . 'App' . ROUTE_PATH);
require_once(BUNDLE_DIR . 'Admin' . ROUTE_PATH);
require_once(BUNDLE_DIR . 'Security' . ROUTE_PATH);



