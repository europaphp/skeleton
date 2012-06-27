<?php

use Container\Europa;

define('EUROPA_START_TIME', microtime());

require_once __DIR__ . '/../app/boot.php';

Europa::fresh()->get('app')->run();