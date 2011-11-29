<?php

use Europa\Application\Container;
use Europa\Fs\Loader;
use Europa\Fs\Locator\PathLocator;
use Testes\Output\Cli as Output;
use Test as Test;

// base path for install
$base = dirname(__FILE__) . '/../';

// make sure we include application configuration
require $base . 'app/bootstrap.php';

// add the tests path
$locator = new PathLocator;
$locator->addPath($base . 'vendor/Testes/lib');
$locator->addPath($base . 'tests');

// register loading
$loader = new Loader;
$loader->setLocator($locator);
$loader->register();

// start covering tests
$coverage = new \Testes\Coverage\Coverage;
$coverage->start();

// run the tests
$tests = new Test;
$tests->run();

$coverage = $coverage->stop();

// output the results in cli format
$output = new Output;
echo $output->render($tests);

$analyzer = new \Testes\Coverage\Analyzer($coverage);
$analyzer->addDirectory(dirname(__FILE__) . '/../lib/Europa');
echo 'Coverage: ' . $analyzer->getPercentage() . '%' . PHP_EOL . PHP_EOL;