<?php

use Europa\App\Container;
use Europa\Fs\Loader;
use Europa\View\ViewScriptInterface;

require_once __DIR__. '/../src/Europa/Fs/Loader.php';

// scrict standards
ini_set('display_errors', 'on');
error_reporting(E_ALL | E_STRICT);

// autoloading
$loader = new Loader;
$loader->getLocator()->addPath(__DIR__ . '/classes');
$loader->register();

// ensure the proper view is set after routing
Container::fetch()->get('app')->event()->bind('route.post', function($app) {
    if ($app->getView() instanceof ViewScriptInterface) {
        $script = $app->getRequest()->isCli() ? 'cli' : 'web';
        $script = $script . '/' . $app->getRequest()->controller;
        $script = str_replace(' ', '/', $script);
        
        $app->getView()->setScript($script);
    }
});