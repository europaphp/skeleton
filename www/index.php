<?php

define('EUROPA_START_TIME', microtime(true));

use Europa\Di\Container;

// can be overridden in bootstrap if need be
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');
date_default_timezone_set('Australia/Sydney');

// bootstrap the app
require dirname(__FILE__) . '/../app/Boot/bootstrap.php';

// any exceptions will routed to the error controller
try {
    $container  = Container::get();
    $request    = $container->request->get();
    $response   = $container->response->get();
    $router     = $container->router->get();
    $dispatcher = $container->dispatcher->get();
    $dispatcher->dispatch($request, $response, $router);
} catch (\Exception $e) {
    $request->setParam('controller', 'error/service-unavailable');
    $dispatcher->dispatch($request, $response);
}