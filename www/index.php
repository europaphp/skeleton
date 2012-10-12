<?php

use Europa\App\App;
use Europa\View\ViewScriptInterface;

define('EUROPA_START_TIME', microtime());

require_once __DIR__ . '/../app/bootstrap.php';

$app = new App;

$app->getEvent()->bind('render.pre', function($app, $view) {
    if ($view instanceof ViewScriptInterface) {
        $view->setScript('web/index');
        $view->getScriptLocator()->setBasePath(__DIR__ . '/../app');
        $view->getScriptLocator()->addPath('demo/views');

        $view->getHelperContainer()->args('Europa\View\Helper\Lang', function() use ($view) {
            return [$view, function($path) {
                return __DIR__ . '/../app/demo/langs/en-us/' . $path . '.ini';
            }];
        });
    }
});

$app();