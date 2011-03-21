<?php

use Europa\Loader;

class Test_Loader extends Testes_Test
{
    private $loader;
    
    public function setUp()
    {
        Loader::register();
    }
    
    public function testRegisterAutoload()
    {
        $funcs = spl_autoload_functions();
        foreach ($funcs as $func) {
            if (is_array($func)
                && $func[0] === 'Europa\Loader'
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
            Loader::search('Europa/Form'),
            'Could not find file.'
        );
    }
    
    public function testLoad()
    {
        $this->assert(
            Loader::load('Europa\Request'),
            'Unable to load class.'
        );
    }
    
    public function testAddPath()
    {
        try {
            Loader::addPath('.');
        } catch (Exception $e) {
            $this->assert(false, 'Could not add load path.');
        }
    }
}