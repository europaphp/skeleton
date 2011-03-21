<?php

use Europa\Request\Http;

class Test_ServiceLocator extends Testes_Test
{
    private $container;
    
    public function setUp()
    {
        $this->container = new Container(
            array(
                'request' => array(
                    array(
                        'test1' => true,
                        'test2' => true
                    )
                ),
                'router' => array()
            )
        );
        $this->container->map('router', '\Europa\Router');
        $this->container->setFormatter(function($service) {
            return \Europa\String::create($service)->toClass() . 'Service';
        });
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
        $this->container->setConfigFor('request', array(array('test2' => false)));
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
    protected function request(array $params = array())
    {
        $request = new Http($this->get('router'));
        foreach ($params as $name => $value) {
            $request->__set($name, $value);
        }
        return $request;
    }
}

class TestService
{
    
}