<?php

namespace Test\All\Fs;
use Europa\Fs\Loader\ClassLoader;
use Europa\Fs\Locator\Locator;
use Testes\Test\UnitAbstract;

class LoaderTest extends UnitAbstract
{
    private $loader;
    
    public function setUp()
    {
        $this->loader = new ClassLoader;
        $this->loader->register();
        $this->loader->getLocator()->add(function($file) {
            return realpath(dirname(__FILE__) . '/../../' . $file . '.php');
        });
    }
    
    public function testRegisterAutoload()
    {
        $funcs = spl_autoload_functions();
        
        foreach ($funcs as $func) {
            if (is_array($func)
                && $func[0] instanceof ClassLoader
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
            $this->loader->__invoke('Europa\Request\Http'),
            'Unable to load class.'
        );
    }
    
    public function testLoadOldStyleNamespacedClass()
    {
        $this->assert(
            $this->loader->__invoke('Test_Provider_Fs_TestClass'),
            'Unable to load old style namespaced class.'
        );
    }
}