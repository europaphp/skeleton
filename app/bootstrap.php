<?php

// scrict standards
ini_set('display_errors', 'on');
error_reporting(E_ALL | E_STRICT);

use Europa\Application\ConfiguratorArray;
use Europa\Application\Container;
use Europa\Fs\Loader;

// makes everything easier to register library loading here
require_once '../lib/Europa/Fs/Loader.php';
$loader = new Loader;
$loader->register();

// uses default configuration, but you can also specify your own
$configuration = new ConfiguratorArray(array(
    '\Europa\Application\Configurator\Basic'
));
$configuration->configure(Container::get());

// kick off autoloading for everything else
Container::get()->loader->get()->register();