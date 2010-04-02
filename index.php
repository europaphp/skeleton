<?php

// error reporting and levels
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

// for reporting puroses
define('EUROPA_START_TIME', microtime());

// require the loader
require 'lib/Europa/Loader.php';

// register autoloading and define paths
Europa_Loader::registerAutoload();
Europa_Loader::addPath('./app/controllers');
Europa_Loader::addPath('./app/plugins');
Europa_Loader::addPath('./lib');

// dispatch the request catching any exceptions
try {
	$europa = new Europa_Request;
	$europa->dispatch();
} catch (Exception $e) {
	throw new Europa_Exception($e->getMessage(), $e->getCode());
}