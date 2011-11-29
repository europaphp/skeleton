<?php

use Europa\Application\Container;
use Europa\Fs\Loader;
use Europa\Fs\Locator\PathLocator;
use Testes\Coverage\Analyzer;
use Testes\Coverage\Coverage;
use Testes\Output\Cli;
use Test;

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
$coverage = new Coverage;
$coverage->start();

// run the tests
$tests = new Test;
$tests->run();

$coverage = $coverage->stop();

// output the results in cli format
$output = new Cli;
echo $output->render($tests);

// analyze and output code coverage
$analyzer = new Analyzer($coverage);
$analyzer->addDirectory(dirname(__FILE__) . '/../lib/Europa');
echo 'Coverage: '
    . $analyzer->getPercentage()
    . '% of lines across '
    . count($analyzer->getTestedFiles())
    . ' of '
    . (count($analyzer->getTestedFiles()) + count($analyzer->getUntestedFiles()))
    . ' files.'
    . PHP_EOL . PHP_EOL;
