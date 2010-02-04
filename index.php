<?php

error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

$time = microtime();

// include the framework
require 'lib/Europa/Controller.php';

// instantiate
$europa = new Europa_Controller;

// dispatch
$europa->dispatch();

echo '<p>Rendered in <strong>' . (microtime() - $time) . '</strong> seconds</p>';