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
Europa_Loader::addPath('./app/helpers');
Europa_Loader::addPath('../lib');

// dispatch the request catching any exceptions
try {
	$europa = new Europa_Request_Http;
	echo $europa->dispatch()->toString();
} catch (Exception $e) {
	echo $e->getMessage();
}