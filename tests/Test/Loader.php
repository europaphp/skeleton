<?php

class Test_Loader extends Testes_Test
{
    private $_dummyDir;
    
    private $_dummyFile;
    
    private $_fileHandle;
    
    public function setUp()
    {
        $this->_dummyDir  = dirname(__FILE__) . '/../DummyNamespace';
        $this->_dummyFile = $this->_dummyDir . DIRECTORY_SEPARATOR . 'LoaderDummyTestClass.php';
        
        mkdir($this->_dummyDir);
        chmod($this->_dummyDir, 0777);
        
        $this->_fileHandle = fopen($this->_dummyFile, 'w+');
        chmod($this->_dummyFile, 0777);
        
        fwrite($this->_fileHandle, '<?php namespace DummyNamespace; class LoaderDummyTestClass {}');
        
        \Europa\Loader::addPath($this->_dummyDir);
    }

    public function tearDown()
    {
        if (file_exists($this->_dummyFile)) {
            fclose($this->_fileHandle);
            unlink($this->_dummyFile);
            unlink($this->_dummyDir);
        }
    }
    
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
            \Europa\Loader::loadClass('DummyNamespace\LoaderDummyTestClass'),
            'Unalbe to load class.'
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