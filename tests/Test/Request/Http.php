<?php

use Europa\Request\Http;

class Test_Request_Http extends Testes_Test
{
    private $request;
    
    public function setUp()
    {
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/EuropaPHP/master/sandbox/index.php';
        $_SERVER['DOCUMENT_ROOT']   = '/var/www/';
        $_SERVER['REQUEST_URI']     = 'EuropaPHP/master/sandbox/some/request/uri';
        
        $this->request = new Http;
    }
    
    public function testRequestHttpRootUri()
    {
        $this->assert(
            $this->request->getRootUri() === 'EuropaPHP/master/sandbox',
            'Root uri is not working.'
        );
    }
    
    public function testRequestHttpRequestUri()
    {
        $this->assert(
            $this->request->getRequestUri() === 'some/request/uri',
            'Request uri is not working.'
        );
    }
}