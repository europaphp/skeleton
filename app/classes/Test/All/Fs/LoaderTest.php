<?php

namespace Test\All\Fs;
use Europa\Fs\Loader;
use Europa\Fs\Locator;
use Testes\Test\UnitAbstract;

class LoaderTest extends UnitAbstract
{
    private $loader;
    
    public function setUp()
    {
        $this->loader = new Loader;
        $this->loader->register();
        $this->loader->getLocator()->addPath(dirname(__FILE__) . '/../..');
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
            $this->loader->load('Test_Provider_Fs_TestClass'),
            'Unable to load old style namespaced class.'
        );
    }
}