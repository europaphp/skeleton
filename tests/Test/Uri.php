<?php

class Test_Uri extends Testes_Test
{
    public function testParameters()
    {
        $uri = new \Europa\Uri;
        
        $uri->test = true;
        $this->assert(isset($uri->test), 'Test parameter was not set.');
        $this->assert($uri->test, 'Test parameter was not set.');
        
        unset($uri->test);
        $this->assert(!$uri->test, 'Test parameter was not unset.');
    }
    
    public function testScheme()
    {
        $uri = new \Europa\Uri;
        
        $uri->setScheme('ftp');
        $this->assert($uri->getScheme() === 'ftp', 'Scheme was not set.');
        $this->assert($uri->getSchemePart() === 'ftp://', 'Scheme part was not formatted properly.');
    }
    
    public function testHost()
    {
        $uri = new \Europa\Uri;
        
        $uri->setHost('localhost');
        $uri->setPort(80);
        $this->assert($uri->getHost() === 'localhost', 'The host was not set properly.');
        $this->assert($uri->getHostPart() === 'localhost:80', 'The host part was not formatted properly.');
        
        $uri->setScheme('https');
        $uri->setPort(443);
        $this->assert($uri->getHostPart() === 'https://localhost', 'The host part was not formatted properly.');
        
        $uri->setPort(444);
        $this->assert($uri->getHostPart() === 'https://localhost:444', 'The host part was not formatted properly.');
    }
    
    public function testPort()
    {
        $uri = new \Europa\Uri;
        
        $uri->setPort('556');
        $this->assert($uri->getPort() === 556, 'The port was not set properly.');
        $this->assert($uri->getPortPart() === ':556', 'The port part was not formatted correctly.');
    }
    
    public function testRequest()
    {
        $uri = new \Europa\Uri;
        
        $uri->setRequest('/my/request/uri/');
        $this->assert($uri->getRequest() === 'my/request/uri', 'Request was not normalized.');
        $this->assert($uri->getRequestPart() === '/my/request/uri', 'Request part was not formatted correctly.');
    }
    
    public function testQuery()
    {
        $uri = new \Europa\Uri;
        
        $uri->setQuery('?test1=0&test2=1');
        $this->assert($uri->test1 === '0', 'Parameter "test1" was not set.');
        $this->assert($uri->test2 === '1', 'Parameter "test2" was not set.');
        $this->assert($uri->getQuery() === 'test1=0&test2=1', 'Query was not formatted properly.');
        $this->assert($uri->getQueryPart() === '?test1=0&test2=1', 'Query part was not formatted properly.');
    }
    
    public function testStringConversion()
    {
        $uri = new \Europa\Uri;
        
        $uri->setScheme('http');
        $this->assert($uri->toString() === '', 'URI should be empty.');
        
        $uri->setHost('127.0.0.1');
        $uri->setPort(80);
        $this->assert($uri->toString() === 'http://127.0.0.1', 'URI should contain correct scheme and host.');
        
        $uri->setPort(8080);
        $this->assert($uri->toString() === 'http://127.0.0.1:8080', 'URI should contain correct scheme, host and port.');
        
        $uri->setRequest('my/request/uri/');
        $this->assert($uri->toString() === 'http://127.0.0.1:8080/my/request/uri', 'URI should contain correct scheme, host, port and request URI.');
        
        $uri->setQuery('?test1=0&test2=1');
        $uri->test3 = 2;
        $this->assert($uri->toString() === 'http://127.0.0.1:8080/my/request/uri?test1=0&test2=1&test3=2', 'URI should contain correct scheme, host, port, request URI and query.');
    }
}