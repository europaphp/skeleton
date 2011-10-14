<?php

namespace Test;
use Europa\Fs\Loader as ClassLoaderObject;
use Europa\Fs\Locator\PathLocator;
use Testes\Test;

class ClassLoader extends Test
{
    private $loader;
    
    public function setUp()
    {
        $this->loader  = new ClassLoaderObject;
        $this->locator = new PathLocator;
        $this->loader->register();
        $this->loader->setLocator($this->locator);
        $this->locator->addPath(dirname(__FILE__) . '/..');
    }
    
    public function testRegisterAutoload()
    {
        $funcs = spl_autoload_functions();
        foreach ($funcs as $func) {
            if (is_array($func)
                && $func[0] instanceof ClassLoaderObject
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
    		$this->loader->load('Provider_ClassLoader_TestClass'),
    		'Unable to load old style namespaced class.'
    	);
    }
}
