<?php

use Europa\Request\Http;
use Europa\ServiceLocator;

// can be overridden in bootstrap if need be
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

// load the bootstrap
require dirname(__FILE__) . '/../app/boot/bootstrap.php';

// any exceptions will routed to the error controller
try {
    $locator = ServiceLocator::getInstance();
    $params  = $locator->router->query(Http::uri());
    
    if (!$params) {
        throw new Exception;
    }
    
    echo $locator->request->setParams($params)->dispatch();
} catch (\Exception $e) {
    die($e);
    echo ServiceLocator::getInstance()->request->setController('error');
}