<?php

error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

require_once dirname(__FILE__) . '/../lib/Europa/Loader.php';
Europa_Loader::registerAutoload();
Europa_Loader::addPath(dirname(__FILE__) . '/../lib');
Europa_Loader::addPath(dirname(__FILE__) . '/app/controllers');
Europa_Loader::addPath(dirname(__FILE__) . '/app/views');

try {
	if (Europa_Request::isCli()) {
		$europa = new Europa_Request_Cli;
		echo trim($europa->dispatch()) . PHP_EOL;
	} else {
		$europa = new Europa_Request_Http;
		echo nl2br(str_replace(' ', '&nbsp;', trim($europa->dispatch())));
	}
} catch (Exception $e) {
	echo $e->getMessage();
}