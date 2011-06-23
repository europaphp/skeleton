<?php

define('START_TIME', microtime(true));

use Europa\Di\Container;

// can be overridden in bootstrap if need be
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');
date_default_timezone_set('Australia/Sydney');

// bootstrap the app
require dirname(__FILE__) . '/../app/Bootstrapper.php';
$boot = new Bootstrapper;
$boot();

// any exceptions will routed to the error controller
$container = Container::get();
$request   = $container->request->get();
$router    = $container->router->get();
try {
    $params = $router->query($request->getUri()->getRequest());
    echo $request->setParams($params)->dispatch()->render();
} catch (\Exception $e) {
    echo $request->setController('error')->dispatch()->render();
}