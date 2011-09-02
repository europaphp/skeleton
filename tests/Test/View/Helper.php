<?php

namespace Test\View;
use Europa\Unit\Test\Test;
use Europa\ServiceLocator;
use Europa\StringObject;
use Europa\View;
use Europa\View\Php;

class Helper extends Test
{
    private $view;
    
    private $locator;
    
    public function setUp()
    {
        $this->view    = new Php;
        $this->locator = new ServiceLocator;
        
        $this->view->setHelperLocator($this->locator);
        $this->locator->setFormatter(function($service) {
            return '\Provider\View\Helper' . StringObject::create($service)->toClass();
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