<?php

// error reporting and levels
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

// for reporting puroses
define('EUROPA_START_TIME', microtime());

// require the loader, register autoloading and define load paths
require dirname(__FILE__) . '/../lib/Europa/Loader.php';
Europa_Loader::registerAutoload();
Europa_Loader::addPath('./app/controllers');
Europa_Loader::addPath('./app/views');
Europa_Loader::addPath('./app/helpers');
Europa_Loader::addPath('../lib');

// dispatch the request catching any exceptions
try {
	$router = new Europa_Router(new Europa_Request_Http);
	$router['default'] = new Europa_Route_Regex(
		'\??/?(?<controller>[^&]+)?',
		'/?/:controller',
		array('controller' => 'index')
	);
	echo $router->setSubject(Europa_Request_Http::getRequestUri())->dispatch();
} catch (Exception $e) {
	// if any errors occur, force the request to the error controller
	$europa = new Europa_Request_Http;
	$europa->controller = 'error';
	$europa->exception  = $e;
	echo $europa->dispatch();
}