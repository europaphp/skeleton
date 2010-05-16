<?php

error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 'on');

require_once dirname(__FILE__) . '/../europa/Europa/Loader.php';
Europa_Loader::registerAutoload();
Europa_Loader::addPath(dirname(__FILE__) . '/../europa');
Europa_Loader::addPath(dirname(__FILE__) . '/lib');
Europa_Loader::addPath(dirname(__FILE__) . '/app/controllers');

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