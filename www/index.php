<?php

use Europa\App\App;
use Europa\View\ViewScriptInterface;

define('EUROPA_START_TIME', microtime());

require_once __DIR__ . '/../app/bootstrap.php';

$app = new App;
$app();