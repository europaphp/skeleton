<?php

use Europa\Fs\Loader;

require_once __DIR__. '/../lib/Europa/Fs/Loader.php';

// scrict standards
ini_set('display_errors', 'on');
error_reporting(E_ALL | E_STRICT);

// autoloading
$loader = new Loader;
$loader->getLocator()->addPath(__DIR__ . '/classes');
$loader->register();