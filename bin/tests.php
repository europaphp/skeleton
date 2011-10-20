<?php

use Europa\Di\Container;
use Testes\Output\Cli as Output;
use Test as Test;

// base path to install
$base = dirname(__FILE__) . '/../';

// all we need to do is include the bootstrap
require $base . 'app/Boot/bootstrap.php';

// add the tests path
$locator = Container::get()->loaderLocator->get();
$locator->addPath($base . 'vendor/Testes/lib');
$locator->addPath($base . 'tests');

// run the tests
$tests = new Test;
$tests->run();

// output the results in cli format
$output = new Output;
echo $output->render($tests);