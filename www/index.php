<?php

define('START_TIME', microtime(true));

use Europa\Request\Http;
use Europa\ServiceLocator;

// can be overridden in bootstrap if need be
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

// bootstrap the app
require dirname(__FILE__) . '/../app/Bootstrapper.php';
$boot = new Bootstrapper;
$boot->boot();

// any exceptions will routed to the error controller
$locator = ServiceLocator::getInstance();
$request = $locator->get('request');
$router  = $locator->get('router');
try {
    $params = $router->query($request->getRequestUri());
    if (!$params) {
        throw new Exception('The required request parameters were not defined.');
    }
    echo $request->setParams($params)->dispatch()->render();
} catch (\Exception $e) {
    echo $request->setController('error')->dispatch()->render();
}