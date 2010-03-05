<?php

// strict error reporting by default
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

// include the loader
require 'lib/Europa/Loader.php';

// register autoloading and define paths
Europa_Loader::registerAutoload();
Europa_Loader::addLoadPath('./lib');

// instantiate
$europa = new Europa_Controller;

// dispatch
$europa->dispatch();