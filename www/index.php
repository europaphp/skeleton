<?php

define('EUROPA_START_TIME', microtime(true));

use Europa\Application\Container;

// bootstrap the app
require dirname(__FILE__) . '/../app/bootstrap.php';

// any exceptions will be routed to the error controller
try {
    $container  = Container::get();
    $request    = $container->getService('request');
    $response   = $container->getService('response');
    $dispatcher = $container->getService('dispatcher');
    $dispatcher->dispatch($request, $response);
} catch (\Exception $e) {
    $request->setParam('controller', 'error');
    $dispatcher->dispatch($request, $response);
}
