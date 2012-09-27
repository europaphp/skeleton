<?php

use Europa\App\Bootstrapper;

// Registers autoloading from the Europa install path.
require_once __DIR__ . '/../src/Europa/Fs/Loader.php';

// Composer dependency autoloader.
require_once __DIR__ . '/../vendor/autoload.php';

// Setup and configure bootstrapper.
$bootstrapper = new Bootstrapper(__DIR__ . '/..', [
    'modules' => ['demo']
]);

// Boot the application.
$bootstrapper->bootstrap();