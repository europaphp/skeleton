<?php

class Test_Loader extends Testes_Test
{
    private $loader;
    
    public function setUp()
    {
        $this->loader = new \Europa\Loader;
        $this->loader->register();
    }
    
    public function testRegisterAutoload()
    {
        $funcs = spl_autoload_functions();
        foreach ($funcs as $func) {
            if (is_array($func)
                && $func[0] === $this->loader
                && $func[1] === 'load'
            ) {
                return;
            }
        }
        $this->assert(false, 'Unable to register autoloading.');
    }
    
    public function testSearch()
    {
        $this->assert(
            $this->loader->search('Europa/Form'),
            'Could not find file.'
        );
    }
    
    public function testLoadClass()
    {
        $this->assert(
            $this->loader->load('Europa\Request'),
            'Unable to load class.'
        );
    }
    
    public function testAddPath()
    {
        try {
            $this->loader->addPath('.');
        } catch (Exception $e) {
            $this->assert(false, 'Could not add load path.');
        }
    }
}