<?php

// so we can report render time
define('EUROPA_START_TIME', microtime(true));

use Europa\Di\Container;

// bootstrap the app
require dirname(__FILE__) . '/../app/bootstrap.php';

// configure and return the dispatcher object
$dispatcher = Container::get()->dispatcher;

// any exceptions will be routed to the error controller
try {
    $dispatcher->dispatch();
} catch (Exception $e) {
    $dispatcher->getRequest()->setParams(array(
        'controller' => 'error',
        'message'    => $e->__toString(),
        'code'       => $e->getCode(),
        'trace'      => $e->getTraceAsString()
    ));
    $dispatcher->disableRouter()->dispatch();
}
