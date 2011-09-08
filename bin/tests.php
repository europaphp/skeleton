<?php

use Europa\Di\Container;
use Testes\Output\Cli;

// all we need to do is include the bootstrap
require dirname(__FILE__) . '/../app/Boot/bootstrap.php';

// add the tests path
$locator = Container::get()->loaderLocator->get();
$locator->addPath(dirname(__FILE__) . '/../tests');

// run the tests
$tests = new \Test;
$tests->run();

// output the results in cli format
$output = new Cli;
echo $output->render($tests);