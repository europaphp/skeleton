<?php

// development
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');
date_default_timezone_set('Australia/Sydney');

// require the bootstrapper and boot the application
require_once dirname(__FILE__) . '/Bootstrapper.php';

use Boot\Bootstrapper;

$boot = new Bootstrapper;
$boot();