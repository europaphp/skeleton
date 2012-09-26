<?php

use Europa\App\Boot;

// Registers autoloading from the Europa install path.
require_once __DIR__ . '/../src/Europa/Fs/Loader.php';

// Composer dependency autoloader.
require_once __DIR__ . '/../vendor/autoload.php';

// Boot!
$boot = new Boot(__DIR__ . '/..', [
    'modules' => ['demo']
]);
$boot->boot();