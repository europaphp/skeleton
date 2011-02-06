<?php

class Test_Loader extends Testes_Test
{
    public function testRegisterAutoload()
    {
        \Europa\Loader::registerAutoload();
        $funcs = spl_autoload_functions();
        foreach ($funcs as $func) {
            if (
                is_array($func)
                && $func[0] === 'Europa\Loader'
                && $func[1] === 'loadClass'
            ) {
                return;
            }
        }
        $this->assert(false, 'Unable to register autoloading.');
    }

    public function testLoadClass()
    {
        $this->assert(
            \Europa\Loader::loadClass('Europa\Request'),
            'Unable to load class.'
        );
    }
    
    public function testSearch()
    {
        $this->assert(
            \Europa\Loader::search('Europa/Form.php'),
            'Could not find file.'
        );
    }
    
    public function testAddPath()
    {
        try {
            \Europa\Loader::addPath('.');
        } catch (Exception $e) {
            $this->assert(false, 'Could not add load path.');
        }
    }
}