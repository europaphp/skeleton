<?php

class Test_ServiceLocator extends Testes_Test
{
    private $container;
    
    public function setUp()
    {
        // set up helper loading
        Container::setDefaultFormatter(function($service) {
            return \Europa\String::create($service)->toClass() . 'Service';
        });
        
        $this->container = new Container(
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
        $this->container->map('router', '\Europa\Router');
    }
    
    public function testDefaultFormatter()
    {
        try {
            if (!$this->container->test instanceof TestService) {
                throw new Exception;
            }
        } catch (Exception $e) {
            $this->assert(false, 'Could not find service class');
        }
    }
    
    public function testSetupConfig()
    {
        $config = $this->container->getConfigFor('request');
        $this->assert(
            $config['class'] === '\Europa\Request\Http',
            'The service container class does not match.'
        );
    }
    
    public function testSetupMap()
    {
        $this->assert(
            $this->container->get('router') instanceof \Europa\Router,
            'The required dependency was not returned.'
        );
    }
    
    public function testOverride()
    {
        $this->assert(
            $this->container->get('request') instanceof \Europa\Request\Http,
            'The required dependency was not returned.'
        );
    }
    
    public function testCustomOverride()
    {
        $valid = $this->container->get('request')->test1 === true
              && $this->container->get('request')->test2 === true;
        $this->assert($valid, 'The request variables were not properly set.');
    }
    
    public function testFreshOverrideWithMergedConfig()
    {
        $this->container->setConfigFor(
            'request',
            array(
                'params' => array(
                    'test2' => false
                )
            )
        );
        
        $this->assert(
            $this->container->get('request')->test1 === true,
            'The configuration was not merged.'
        );
        
        $this->assert(
            $this->container->get('request')->test2 === false,
            'The configuration was not merged.'
        );
    }
}

class Container extends \Europa\ServiceLocator
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

class TestService
{
    
}