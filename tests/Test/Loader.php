<?php

class Test_Loader extends Testes_Test
{
	private $_fileHandle;
	
	private $_dummyFile;
	
	private $_dummyCLass;
	
	public function setUp()
	{
		$this->_dummyFile  = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'LoaderDummyTestClass.php';
		$this->_dummyClass = 'Test_LoaderDummyTestClass';
		$this->_fileHandle = fopen($this->_dummyFile, 'w+');
		fwrite($this->_fileHandle, '<?php class ' . $this->_dummyClass . ' {}');
		chmod($this->_dummyFile, 0777);
	}

	public function tearDown()
	{
	    if (file_exists($this->_dummyFile)) {
    		fclose($this->_fileHandle);
    		unlink($this->_dummyFile);
    	}
	}
	
	public function testRegisterAutoload()
	{
		Europa_Loader::registerAutoload();
		$funcs = spl_autoload_functions();
		foreach ($funcs as $func) {
			if (
				is_array($func)
				&& $func[0] === 'Europa_Loader'
				&& $func[1] === 'loadClass'
			) {
			    return;
			}
		}
		$this->assert(false, 'Unable to regiseter autoloading.');
	}

	public function testLoadClass()
	{
		$this->assert(
		    Europa_Loader::loadClass($this->_dummyClass),
		    'Unalbe to load class.'
		);
	}
	
	public function testAddPath()
	{
	    try {
    		Europa_Loader::addPath('.');
    	} catch (Exception $e) {
    	    $this->assert(false, 'Could not add load path.');
    	}
	}
}