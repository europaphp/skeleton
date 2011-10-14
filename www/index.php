<?php

define('EUROPA_START_TIME', microtime(true));

use Europa\Di\Container;

// bootstrap the app
require dirname(__FILE__) . '/../app/Boot/bootstrap.php';

// any exceptions will be routed to the error controller
try {
    $container  = Container::get();
    $request    = $container->request->get();
    $response   = $container->response->get();
    $dispatcher = $container->dispatcher->get();
    $dispatcher->dispatch($request, $response);
} catch (\Exception $e) {
    $request->setParam('controller', 'error');
    $dispatcher->dispatch($request, $response);
}