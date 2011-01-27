<?php

// for showing off
define('EUROPA_START_TIME', microtime());

// load the bootstrap
require dirname(__FILE__) . '/../app/boot/bootstrap.php';

// dispatch the request catching any exceptions
try {
    // request routing
    $router = new Europa\Router\Request(new Europa\Request\Http);
    
    // default route matches root/uri/index.php/request/uri to Request\UriController
    $router['default'] = new Europa\Route\Regex(
        'index\.php/(?<controller>.+)',
        'index.php/:controller',
        array('controller' => 'index')
    );
    
    // dispatch and echo the result
    echo $router->dispatch();
} catch (Exception $e) {
	$error = new ErrorController(new Europa\Request\Http);
	$error->exception = $e;
	echo $error;
}