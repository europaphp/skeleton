<?php

// all we need to do is include the bootstrap
require dirname(__FILE__) . '/../app/Bootstrapper.php';
$boot = new Bootstrapper;
$boot->boot();

$loader = new \Europa\Loader;
$loader->addPath(dirname(__FILE__) . '/../tests');
$loader->register();

$tests = new \Test;
$tests->run();

if ($assertions = $tests->assertions()) {
    echo "Tests failed:\n";
    foreach ($assertions as $assertion) {
        echo '  '
           , $assertion->getTestFile()
           , '('
           , $assertion->getTestLine()
           , '): '
           , $assertion->getMessage()
           , ' In: '
           , $assertion->getTestClass()
           , '->'
           , $assertion->getTestMethod()
           , "\n";
    }
} else {
    echo "Tests passed!\n";
}