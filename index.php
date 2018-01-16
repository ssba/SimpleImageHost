<?php

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Autoload.php';
require_once __DIR__ . '/vendor/phpqrcode.php';

$autoloader = new Autoload();
$autoloader->addNamespace('Core', __DIR__ . '/core');
$autoloader->addNamespace('App', __DIR__ . '/app');
$autoloader->register();

$app = new App\Application();
$app->run();




