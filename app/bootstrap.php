<?php

// scrict standards
ini_set('display_errors', 'on');
error_reporting(E_ALL | E_STRICT);

use Europa\Di\Configuration\Standard;
use Europa\Di\Container;
use Europa\Fs\Loader;

// makes everything easier to register library loading here
require_once dirname(__FILE__) . '/../lib/Europa/Fs/Loader.php';
$loader = new Loader;
$loader->register();

// uses default configuration, but you can also specify your own
$config = new Standard;
$config->configure(Container::get());

// kick off autoloading for everything else
Container::get()->getService('loader')->register();