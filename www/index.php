<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Europa\App\App([
    'modules' => ['main', 'help', 'tests']
]);

$app();