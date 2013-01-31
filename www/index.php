<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Europa\App\App(__DIR__ . '/../europaphp.yml');
$app->save();
$app();