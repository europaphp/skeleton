<?php

namespace Test\View;
use Europa\Di\Container;
use Europa\Fs\Locator\PathLocator;
use Europa\StringObject;
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
        $container->setFormatter(function($dep) {
            return '\Provider\View' . StringObject::create($dep)->toClass() . 'Helper';
        });
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
