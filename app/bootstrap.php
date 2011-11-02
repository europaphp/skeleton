<?php

use Europa\Configuration;
use Europa\Di\Container;
use Europa\Fs\Loader;

require_once '../lib/Europa/Fs/Loader.php';
$loader = new \Europa\Fs\Loader;
$loader->register();

$configuration = new Configuration\DefaultConfiguration;
$configuration->configure(Container::get());
