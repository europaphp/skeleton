<?php

error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

$time = microtime();

// include the framework
require 'lib/Europa/Loader.php';

// register autoloading
Europa_Loader::registerAutoload();

// add the current load path
Europa_Loader::addLoadPath('./lib');

// instantiate
$europa = new Europa_Controller;

// dispatch
$europa->dispatch();

echo '<p>Rendered in <strong>' . (microtime() - $time) . '</strong> seconds</p>';