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
        $this->_container->map('router', '\Europa\Router');
    }
    
    public function testSetupConfig()
    {
        $config = $this->_container->getConfigFor('request');
        $this->assert(
            $config['class'] === '\Europa\Request\Http',
            'The service container class does not match.'
        );
    }
    
    public function testSetupMap()
    {
        $this->assert(
            $this->_container->get('router') instanceof \Europa\Router,
            'The required dependency was not returned.'
        );
    }
    
    public function testOverride()
    {
        $this->assert(
            $this->_container->get('request') instanceof \Europa\Request\Http,
            'The required dependency was not returned.'
        );
    }
    
    public function testCustomOverride()
    {
        $valid = $this->_container->get('request')->test1 === true
              && $this->_container->get('request')->test2 === true;
        $this->assert($valid, 'The request variables were not properly set.');
    }
    
    public function testFreshOverrideWithMergedConfig()
    {
        $this->_container->setConfigFor(
            'request',
            array(
                'params' => array(
                    'test2' => false
                )
            )
        );
        
        $this->assert(
            $this->_container->get('request')->test1 === true,
            'The configuration was not merged.'
        );
        
        $this->assert(
            $this->_container->get('request')->test2 === false,
            'The configuration was not merged.'
        );
    }
}

class Container extends \Europa\ServiceContainer
{
    protected function request($class, array $params = array())
    {
        $request = new $class($this->get('router'));
        foreach ($params as $name => $value) {
            $request->__set($name, $value);
        }
        return $request;
    }
}