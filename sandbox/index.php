<?php

// error reporting and levels
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

// for reporting puroses
define('EUROPA_START_TIME', microtime());

// require the loader, register autoloading and define load paths
$base = dirname(__FILE__) . '/';
require $base . '/../lib/Europa/Loader.php';
Europa_Loader::registerAutoload();
Europa_Loader::addPath($base . 'app/controllers');
Europa_Loader::addPath($base . 'app/views');
Europa_Loader::addPath($base . 'app/helpers');

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