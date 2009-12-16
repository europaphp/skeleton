<?php

$__time = microtime();

// include the framework
require 'lib/Europa/Controller.php';

// instantiate
$europa = new Europa_Controller;

// dispatch
$europa->dispatch();

echo '<p>Rendered in ' . (microtime() - $__time) . ' seconds. Using ' . (memory_get_usage() / (1024 * 1024)) . ' megabytes of memory.</p>';