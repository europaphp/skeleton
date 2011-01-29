<?php

class Test_ServiceContainer extends Testes_Test
{
    private $_container;
    
    public function setUp()
    {
        $this->_container = new Container(
            array(
                'request' => array(
                    'class'  => '\Europa\Request\Http',
                    'params' => array(
                        'test1' => true,
                        'test2' => true
                    )
                ),
                'router' => array()
            )
        );
        $this->_container->router = '\Europa\Router';
    }
    
    public function testSetupConfig()
    {
        $this->assert(
            $this->_container['request']['class'] === '\Europa\Request\Http',
            'The service container class does not match.'
        );
    }
    
    public function testSetupMap()
    {
        $this->assert(
            $this->_container->router instanceof \Europa\Router,
            'The required dependency was not returned.'
        );
    }
    
    public function testOverride()
    {
        $this->assert(
            $this->_container->request instanceof \Europa\Request\Http,
            'The required dependency was not returned.'
        );
    }
    
    public function testCustomOverride()
    {
        $valid = $this->_container->request->test1 === true
              && $this->_container->request->test2 === true;
        $this->assert($valid, 'The request variables were not properly set.');
    }
    
    public function testFreshOverrideWithMergedConfig()
    {
        $configOverride = array(
            'params' => array(
                'test2' => false
            )
        );
        
        $request = $this->_container->request($configOverride);
        $valid   = $request->test1 === true
                && $request->test2 === false;
        $this->assert($valid, 'Configuration overriding is not working.');
    }
    
    public function testCache()
    {
        $this->assert(
            isset($this->_container->request),
            'Dependency caching is not working.'
        );
    }
    
    public function testUnset()
    {
        unset($this->_container->request);
        $this->assert(
            !isset($this->_container->request),
            'Unsetting is not working.'
        );
    }
}

class Container extends \Europa\ServiceContainer
{
    protected function request(array $config = array())
    {
        $request = new $config['class'];
        foreach ($config['params'] as $name => $value) {
            $request->__set($name, $value);
        }
        return $request;
    }
}