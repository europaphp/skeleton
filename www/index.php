<?php

define('EUROPA_START_TIME', microtime());

require_once __DIR__ . '/../app/bootstrap.php';

Europa::main()->app->run();