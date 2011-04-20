<?php

use Europa\ServiceLocator;
use Europa\String;
use Europa\View;
use Europa\View\Php;

class Test_View_Helper extends Testes_Test
{
    private $view;
    
    private $locator;
    
    public function setUp()
    {
        $this->view    = new Php;
        $this->locator = new ServiceLocator;
        
        $this->view->setHelperLocator($this->locator);
        $this->locator->setFormatter(function($service) {
            return String::create($service)->toClass() . 'ServiceInjector';
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
    public function __construct(View $view)
    {
        
    }
    
    public function test()
    {
        return true;
    }
}