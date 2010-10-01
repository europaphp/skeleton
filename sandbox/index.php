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
    $request = new Europa_Request_Http;
	$router  = new Europa_Router();
	$router['default'] = new Europa_Route_Regex(
		'\??/?(?<controller>[^&]+)?',
		'/?/:controller',
		array('controller' => 'index')
	);
	echo $request->setRouter($router)->dispatch();
} catch (Exception $e) {
	// if any errors occur, force the request to the error controller
	$europa = new Europa_Request_Http;
	$europa->controller = 'error';
	$europa->exception  = $e;
	echo $europa->dispatch();
}