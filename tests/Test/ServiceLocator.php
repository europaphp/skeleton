<?php

namespace Test;
use Europa\Request\Http;
use Europa\Router;
use Europa\String;
use Europa\Unit\Test\Test;
use Provider\ServiceLocator\Locator;
use Provider\ServiceLocator\TestService;

class ServiceLocator extends Test
{
    private $container;
    
    public function setUp()
    {
        $this->container = new Locator(
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
            return '\Provider\ServiceLocator' . String::create($service)->toClass() . 'Service';
        });
    }
    
    public function testDefaultFormatter()
    {
        try {
            if (!$this->container->test instanceof TestService) {
                throw new \Exception;
            }
        } catch (\Exception $e) {
            $this->assert(false, 'Could not find service class');
        }
    }
    
    public function testSetupMap()
    {
        $this->assert(
            $this->container->get('router') instanceof Router,
            'The required dependency was not returned.'
        );
    }
    
    public function testOverride()
    {
        $this->assert(
            $this->container->get('request') instanceof Http,
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