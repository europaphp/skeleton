<?php

namespace Test;
use Europa\Unit\Test\Test;
use Europa\Loader as LoaderObject;

class Loader extends Test
{
    private $loader;
    
    public function setUp()
    {
        LoaderObject::register();
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
            LoaderObject::search('Europa/Form'),
            'Could not find file.'
        );
    }
    
    public function testLoad()
    {
        $this->assert(
            LoaderObject::load('Europa\Request'),
            'Unable to load class.'
        );
    }
    
    public function testAddPath()
    {
        try {
            LoaderObject::addPath('.');
        } catch (\Exception $e) {
            $this->assert(false, 'Could not add load path.');
        }
    }
}