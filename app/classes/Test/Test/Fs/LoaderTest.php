<?php

namespace Test\Test\Fs;
use Europa\Fs\Loader;
use Europa\Fs\Locator;
use Testes\Test\Test;

class LoaderTest extends Test
{
    private $loader;
    
    public function setUp()
    {
        $this->loader  = new Loader;
        $this->locator = new Locator;
        $this->loader->register();
        $this->loader->setLocator($this->locator);
        $this->locator->addPath(dirname(__FILE__) . '/../..');
    }
    
    public function testRegisterAutoload()
    {
        $funcs = spl_autoload_functions();
        foreach ($funcs as $func) {
            if (is_array($func)
                && $func[0] instanceof Loader
                && $func[1] === 'load'
            ) {
                return;
            }
        }
        $this->assert(false, 'Unable to register autoloading.');
    }
    
    public function testLoadClass()
    {
        $this->assert(
            $this->loader->load('Europa\Request\Http'),
            'Unable to load class.'
        );
    }
    
    public function testLoadOldStyleNamespacedClass()
    {
        $this->assert(
            $this->loader->load('Provider_Fs_TestClass'),
            'Unable to load old style namespaced class.'
        );
    }
}