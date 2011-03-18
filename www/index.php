<?php

// can be overridden in bootstrap if need be
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

// load the bootstrap
require dirname(__FILE__) . '/../app/boot/bootstrap.php';

// any exceptions will routed to the error controller
try {
    echo \Europa\ServiceLocator::getInstance()->router;
} catch (\Exception $e) {
    echo \Europa\ServiceLocator::getInstance()->request->setController('error');
}