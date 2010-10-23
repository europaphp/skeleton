<?php

class Test_ServiceContainer extends Europa_Unit_Test
{
    private $_container;
    
    public function setUp()
    {
        $this->_container = new Container(
            array(
                'request' => array(
                    'class'  => 'Europa_Request_Http',
                    'params' => array(
                        'test1' => true,
                        'test2' => true
                    )
                ),
                'router' => array()
            )
        );
        $this->_container->router = 'Europa_Router';
    }
    
    public function testSetupConfig()
    {
        return $this->_container['request']['class'] === 'Europa_Request_Http';
    }
    
    public function testSetupMap()
    {
        return $this->_container->router instanceof Europa_Router;
    }
    
    public function testOverride()
    {
        return $this->_container->request instanceof Europa_Request_Http;
    }
    
    public function testCustomOverride()
    {
        return $this->_container->request->test1 === true
            && $this->_container->request->test2 === true;
    }
    
    public function testFreshOverrideWithMergedConfig()
    {
        $configOverride = array(
            'params' => array(
                'test2' => false
            )
        );
        
        $request = $this->_container->request($configOverride);
        return $request->test1 === true
            && $request->test2 === false;
    }
    
    public function testCache()
    {
        return isset($this->_container->request);
    }
    
    public function testUnset()
    {
        unset($this->_container->request);
        return !isset($this->_container->request);
    }
}

class Container extends Europa_ServiceContainer
{
    protected function request(array $config = array())
    {
        $request = new $config['class'];
        $request->setRouter($this->router);
        foreach ($config['params'] as $name => $value) {
            $request->setParam($name, $value);
        }
        return $request;
    }
}