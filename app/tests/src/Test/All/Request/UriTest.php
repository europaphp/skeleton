<?php

namespace Test\All\Request;
use Europa\Request\Uri;
use Testes\Test\UnitAbstract;

class UriTest extends UnitAbstract
{
    public function parameterSetAndGet()
    {
        $uri = new Uri;
        
        $uri->test = true;
        $this->assert(isset($uri->test), 'Test parameter was not set.');
        $this->assert($uri->test, 'Test parameter was not set.');
        
        unset($uri->test);
        $this->assert(!$uri->test, 'Test parameter was not unset.');
    }
    
    public function schemeSetAndGet()
    {
        $uri = new Uri;
        
        $uri->setScheme('ftp');
        $this->assert($uri->getScheme() === 'ftp', 'Scheme was not set.');
        $this->assert($uri->getSchemePart() === 'ftp://', 'Scheme part was not formatted properly.');
    }
    
    public function hostSetAndGet()
    {
        $uri = new Uri;
        
        $uri->setHost('localhost');
        $uri->setPort(80);
        $this->assert($uri->getHost() === 'localhost', 'The host was not set properly.');
        $this->assert($uri->getHostPart() === 'http://localhost', 'The host part was not formatted properly.');
        
        $uri->setScheme('https');
        $uri->setPort(443);
        $this->assert($uri->getHostPart() === 'https://localhost', 'The host part was not formatted properly.');
        
        $uri->setPort(444);
        $this->assert($uri->getHostPart() === 'https://localhost:444', 'The host part was not formatted properly.');
        
        $uri->setUsername('me');
        $this->assert($uri->getHostPart() === 'https://me@localhost:444', 'The auth part was not formatted properly.');
        
        $uri->setPassword('you');
        $this->assert($uri->getHostPart() === 'https://me:you@localhost:444', 'The auth part was not formatted properly.');
    }
    
    public function portSetAndGet()
    {
        $uri = new Uri;
        
        $uri->setPort('556');
        $this->assert($uri->getPort() === 556, 'The port was not set properly.');
        $this->assert($uri->getPortPart() === ':556', 'The port part was not formatted correctly.');
    }
    
    public function requestSetAndGet()
    {
        $uri = new Uri;
        
        $uri->setRequest('/my/request/uri/');
        $this->assert($uri->getRequest() === 'my/request/uri', 'Request was not normalized.');
        $this->assert($uri->getRequestPart() === '/my/request/uri', 'Request part was not formatted correctly.');
    }
    
    public function querySetAndGet()
    {
        $uri = new Uri;
        
        $uri->setQuery('?test1=0&test2=1');
        $this->assert($uri->test1 === '0', 'Parameter "test1" was not set.');
        $this->assert($uri->test2 === '1', 'Parameter "test2" was not set.');
        $this->assert($uri->getQuery() === 'test1=0&test2=1', 'Query was not formatted properly.');
        $this->assert($uri->getQueryPart() === '?test1=0&test2=1', 'Query part was not formatted properly.');
    }
    
    public function fragmentSetAndGet()
    {
        $uri = new Uri;
        
        $uri->setFragment('grenade');
        $this->assert($uri->getFragment() === 'grenade', 'Fragment was not set properly.');
        $this->assert($uri->getFragmentPart() === '#grenade', 'Fragment was not formatted properly');
    }
    
    public function conversionToString()
    {
        $uri = new Uri;
        
        $uri->setScheme('http');
        $this->assert(!$uri->__toString(), 'The URI should be empty.');
        
        $uri->setHost('127.0.0.1');
        $uri->setUsername('user');
        $uri->setPassword('pass');
        $uri->setPort(80);
        $this->assert($uri->__toString() === 'http://user:pass@127.0.0.1', 'URI should contain correct scheme and host.');
        
        $uri->setPort(8080);
        $this->assert($uri->__toString() === 'http://user:pass@127.0.0.1:8080', 'URI should contain correct scheme, host and port.');
        
        $uri->setRequest('my/request/uri/');
        $this->assert($uri->__toString() === 'http://user:pass@127.0.0.1:8080/my/request/uri', 'URI should contain correct scheme, host, port and request.');
        
        $uri->setQuery('?test1=0&test2=1');
        $uri->test3 = 2;
        $this->assert($uri->__toString() === 'http://user:pass@127.0.0.1:8080/my/request/uri?test1=0&test2=1&test3=2', 'URI should contain correct scheme, host, port, request and query.');
        
        $uri->setFragment('grenade');
        $this->assert($uri->__toString() === 'http://user:pass@127.0.0.1:8080/my/request/uri?test1=0&test2=1&test3=2#grenade', 'URI should contain correct scheme, host, port, request, query and fragment.');
    }
    
    public function fromString()
    {
        $uri = new Uri('http://trey:shugart@europaphp.org:80/documentation?component=Uri#properties');
        $this->assert($uri->getScheme() === 'http', 'Scheme was not parsed properly.');
        $this->assert($uri->getUsername() === 'trey', 'Username was not parsed properly.');
        $this->assert($uri->getPassword() === 'shugart', 'Password was not parsed properly.');
        $this->assert($uri->getHost() === 'europaphp.org', 'Host was not parsed properly.');
        $this->assert($uri->getPort() === 80, 'Port was not parsed properly.');
        $this->assert($uri->getRequest() === 'documentation', 'Request was not parsed properly.');
        $this->assert($uri->getQuery() === 'component=Uri', 'Query was not parsed properly.');
        $this->assert($uri->getFragment() === 'properties', 'Fragment was not parsed properly.');
    }

    public function suffix()
    {
        $uri = new Uri('http://test.com/test.php');
        
        // make sure the proper initial values are parsed
        $this->assert($uri->getSuffix() === 'php', 'The suffix should be "php".');
        $this->assert($uri->getRequest() === 'test', 'The request should be "test.php".');
        
        // reset the suffix and make sure it was replaced
        $uri->setSuffix('html');
        $this->assert($uri->getSuffix() === 'html', 'The suffix should have been changed to "html".');
        $this->assert($uri->getRequest() === 'test', 'The request should have been changed to "test.html".');
    }
}