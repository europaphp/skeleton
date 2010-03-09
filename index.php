<?php

// require the loader
require 'lib/Europa/Loader.php';

// register autoloading and define paths
Europa_Loader::registerAutoload();
Europa_Loader::addPath('./app/controllers');
Europa_Loader::addPath('./app/plugins');
Europa_Loader::addPath('./lib');

// dispatch the request catching any exceptions
try {
	$europa = new Europa_Controller;
	$europa->dispatch();
} catch (Europa_Exception $e) {
	die($e->getMessage());
}