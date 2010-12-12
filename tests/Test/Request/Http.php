<?php

class Test_Request_Http extends Testes_Test
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
		$this->assert(
		    Europa_Request_Http::root() === 'EuropaPHP/master/sandbox',
		    'Root uri is not working.'
		);
	}
	
	public function testRequestHttpRequestUri()
	{
	    $this->assert(
		    Europa_Request_Http::uri() === 'some/request/uri',
		    'Request uri is not working.'
		);
	}
}