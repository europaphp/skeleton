<?php

class Test_Request_Http extends Europa_Unit_Test
{
	public function setUp()
	{
		$_SERVER['PHP_SELF']    = 'master/index.php';
		$_SERVER['REQUEST_URI'] = 'master/request/uri/';
		$this->_request = new Europa_Request_Http;
	}
	
	public function testRequestHttpRootUri()
	{
		return Europa_Request_Http::getRootUri() === 'master';
	}
	
	public function testRequestHttpRequestUri()
	{
		return Europa_Request_Http::getRequestUri() === 'request/uri';
	}
	
	public function testUriFormatting()
	{
		$uri1 = $this->_request->formatUri('/my/new/uri/');
		$uri2 = $this->_request->formatUri('my/new/uri');
		return $uri1 === '/master/my/new/uri/'
		    && $uri2 === '/master/my/new/uri';
	}
}