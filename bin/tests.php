<?php

use Europa\Application\Container;
use Europa\Fs\Loader;
use Europa\Fs\Locator\PathLocator;
use Testes\Output\Cli as Output;
use Test as Test;

// base path for install
$base = dirname(__FILE__) . '/../';

// make sure we include application configuration
require $base . 'app/Boot/bootstrap.php';

// add the tests path
$locator = new PathLocator;
$locator->addPath($base . 'vendor/Testes/lib');
$locator->addPath($base . 'tests');

// register loading
$loader = new Loader;
$loader->setLocator($locator);
$loader->register();

// run the tests
$tests = new Test;
$tests->run();

// output the results in cli format
$output = new Output;
echo $output->render($tests);