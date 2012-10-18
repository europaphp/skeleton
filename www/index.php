<?php

define('EUROPA_START_TIME', microtime());

require_once __DIR__ . '/../vendor/autoload.php';

$app   = new Europa\App\App(['paths.root' => __DIR__ . '/..']);
$app[] = 'demo';
$app();