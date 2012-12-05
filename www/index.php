<?php

define('EUROPA_START_TIME', microtime());

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Europa\App\App([
    'appPath' => __DIR__ . '/../app',
    'modules' => ['main', 'help', 'tests']
]);

$app();