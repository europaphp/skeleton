<?php

use Europa\App\Container;
use Europa\Fs\Loader;
use Europa\View\ViewScriptInterface;

require_once __DIR__ . '/../src/Europa/Fs/Loader.php';
require_once __DIR__ . '/../vendor/autoload.php';

// scrict standards
ini_set('display_errors', 'on');
error_reporting(E_ALL | E_STRICT);

// autoloading
$loader = new Loader;
$loader->getLocator()->addPath(__DIR__ . '/classes');
$loader->register();

// set up the main container
Container::init(['root' => __DIR__ . '/..']);

// ensure the proper view is set after routing
Container::get()->app->event()->bind('route.post', function($app) {
    if ($app->getView() instanceof ViewScriptInterface) {
        $script = $app->getRequest()->isCli() ? 'cli' : 'web';
        $script = $script . '/' . $app->getRequest()->controller;
        $script = str_replace(' ', '/', $script);
        
        $app->getView()->setScript($script);
    }
});