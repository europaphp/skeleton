<?php

// for showing off
define('EUROPA_START_TIME', microtime());

// load the bootstrap
require dirname(__FILE__) . '/../app/boot/bootstrap.php';

// dispatch the request catching any exceptions
try {
    // request routing
    $router = new Europa_Router_Request(new Europa_Request_Http);
    
    // default route matches root/uri/index.php/request/uri to Request_UriController
    $router['default'] = new Europa_Route_Regex(
        'index\.php/(?<controller>.+)',
        'index.php/:controller',
        array('controller' => 'index')
    );
    
    // dispatch and echo the result
    echo $router->dispatch();
} catch (Exception $e) {
	$error = new ErrorController(new Europa_Request_Http);
	$error->exception = $e;
	echo $error;
}