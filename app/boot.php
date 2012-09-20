<?php

// Registers autoloading from the Europa install path.
require_once __DIR__ . '/../src/Europa/Fs/Loader.php';

// Composer dependency autoloader.
require_once __DIR__ . '/../vendor/autoload.php';

// Boot!
(new Europa\App\Boot(__DIR__ . '/..'))->boot();