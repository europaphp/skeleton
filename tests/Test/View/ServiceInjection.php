<?php

class Test_View_ServiceInjection extends Testes_Test
{
    private $view;
    
    private $locator;
    
    public function setUp()
    {
        $this->view    = new \Europa\View\Php;
        $this->locator = new \Europa\ServiceLocator;
        
        $this->view->setServiceLocator($this->locator);
        $this->locator->setFormatter(function($service) {
            return \Europa\String::create($service)->toClass() . 'ServiceInjector';
        });
    }
    
    public function testNewInstanceRetrieval()
    {
        $this->assert($this->view->test()->test(), 'The service injector class was not found.');
    }
    
    public function testCachedInstanceRetrieval()
    {
        $this->assert($this->view->test->test(), 'The service injector class was not found.');
    }
}

class TestServiceInjector
{
    public function __construct(\Europa\View $view)
    {
        
    }
    
    public function test()
    {
        return true;
    }
}