<?php

use Europa\App\Container;

define('EUROPA_START_TIME', microtime());

require_once __DIR__ . '/../app/boot.php';

Container::europa()->app->run();