<?php

// can be overridden in bootstrap if need be
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

// start benchmark
require_once('../lib/Europa/Bench.php');
$bench = new \Europa\Bench;

// load the bootstrap
require dirname(__FILE__) . '/../app/boot/bootstrap.php';

// so we can view the results in the view
\Europa\Registry::set('bench', $bench);

// any exceptions will routed to the error controller
try {
    echo \Europa\ServiceLocator::getInstance()->router;
} catch (\Exception $e) {
    echo \Europa\ServiceLocator::getInstance()->request->setController('error');
}