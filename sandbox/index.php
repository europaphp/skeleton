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
	$europa = new Europa_Request_Http;
	$router = new Europa_Router;
	
	// add an example route for matching the first part of the query string
	$router->setRoute(
		'default',
		new Europa_Router_Route_Regex(
			'\?/?(?<controller>[^&]*)',
			'/?/:controller'
		)
	);
	
	// set the params matched by the router, if matched
	// this will also set the controller if found
	if ($router->route($europa)) {
		$europa->setParams($router->getParams());
	}
	
	// the dispatch method returns the controller which has  __toString()
	echo $europa->dispatch();
} catch (Exception $e) {
	// if any errors occur, force the request to the error controller
	$europa = new Europa_Request_Http;
	$europa->controller = 'error';
	$europa->exception  = $e;
	echo $europa->dispatch();
}