<?php

namespace Test;
use Europa\ClassLoader as ClassLoaderObject;
use Testes\Test;

class ClassLoader extends Test
{
    private $loader;
    
    public function setUp()
    {
        $this->loader = new ClassLoaderObject;
        $this->loader->register();
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
    
    public function testLoad()
    {
        $this->assert(
            $this->loader->load('Europa\Request\Http'),
            'Unable to load class.'
        );
    }
}