<?php

namespace Test\View;
use Europa\Application\Container;
use Europa\Filter\CallbackFilter;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Locator\PathLocator;
use Europa\View\Php;
use Provider\View\TestHelper;
use Testes\Test;

class Helper extends Test
{
    private $view;
    
    private $locator;
    
    public function setUp()
    {
        $this->view = new Php(new PathLocator);
        $container  = new Container;
        
        $this->view->setHelperContainer($container);
        $container->setFilter(new CallbackFilter(function($dep) {
            $classNameFilter = new ClassNameFilter;
            return '\Provider\View' . $classNameFilter->filter($dep) . 'Helper';
        }));
    }
    
    public function testNewInstanceRetrieval()
    {
        $this->assert($this->view->test() instanceof TestHelper, 'The container dependency class was not found.');
    }
    
    public function testCachedInstanceRetrieval()
    {
        $this->assert($this->view->test instanceof TestHelper, 'The container dependency class was not found.');
    }
}
