<?php

define('EUROPA_START_TIME', microtime());

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Europa\App\App([
    'modules' => ['main', 'help', 'tests']
]);

$app->save();
$app();