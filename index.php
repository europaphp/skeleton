<?php

// strict error reporting by default
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

// include the loader
require 'lib/Europa/Loader.php';

// register autoloading
Europa_Loader::registerAutoload();

// add the current load path
Europa_Loader::addLoadPath('./lib');

// instantiate
$europa = new Europa_Controller;

// dispatch
$europa->dispatch();