<?php

require_once dirname(__FILE__) . '/../europa/lib/Europa/Loader.php';
Europa_Loader::registerAutoload();
Europa_Loader::addPath(dirname(__FILE__) . '/../europa/lib');
Europa_Loader::addPath(dirname(__FILE__) . '/lib');
Europa_Loader::addPath(dirname(__FILE__) . '/app/controllers');

try {
	if (Europa_Request::isCli()) {
		$europa = new Europa_Request_Cli;
		echo trim($europa->dispatch()) . PHP_EOL;
	} else {
		$europa = new Europa_Request_Http;
		echo nl2br(trim($europa->dispatch()));
	}
} catch (Exception $e) {
	echo $e->getMessage();
}