<?php

class Test_Loader extends Europa_Unit_Test
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
	}

	public function tearDown()
	{
		fclose($this->_fileHandle);
		unlink($this->_dummyFile);
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
				return true;
			}
		}
		return false;
	}

	public function testLoadClass()
	{
		return (bool) Europa_Loader::loadClass($this->_dummyClass);
	}
	
	public function testAddPath()
	{
		Europa_Loader::addPath('.');
		return true;
	}
}