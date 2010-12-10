<?php

class Test_Request_Http extends Europa_Unit_Test
{
	public function setUp()
	{
		$_SERVER['SCRIPT_FILENAME'] = '/var/www/EuropaPHP/master/sandbox/index.php';
		$_SERVER['DOCUMENT_ROOT']   = '/var/www/';
		$_SERVER['REQUEST_URI']     = 'EuropaPHP/master/sandbox/some/request/uri';
		$this->_request = new Europa_Request_Http;
	}
	
	public function testRequestHttpRootUri()
	{
		return Europa_Request_Http::getRootUri() === 'EuropaPHP/master/sandbox';
	}
	
	public function testRequestHttpRequestUri()
	{
		return Europa_Request_Http::getRequestUri() === 'some/request/uri';
	}
	
	public function testUriFormatting()
	{
		$uri1 = $this->_request->formatUri('/my/new/uri/');
		$uri2 = $this->_request->formatUri('my/new/uri');
		return $uri1 === '/EuropaPHP/master/sandbox/my/new/uri/'
		    && $uri2 === '/EuropaPHP/master/sandbox/my/new/uri';
	}
}