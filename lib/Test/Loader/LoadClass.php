<?php

class Test_Loader_LoadClass extends Europa_Unit_Test
{
	private $_fileHandle;
	
	private $_dummyFile;
	
	private $_dummyCLass;
	
	public function setUp()
	{
		$this->_dummyFile  = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DummyTestClass.php';
		$this->_dummyClass = 'Test_Loader_DummyTestClass';
		$this->_fileHandle = fopen($this->_dummyFile, 'w+');
		//fwrite($this->_fileHandle, 'class ' . $this->_dummyClass . ' {}');
	}

	public function tearDown()
	{
		@fclose($this->_fileHandle);
		@unlink($this->_dummyFile);
	}

	public function run()
	{
		return (bool) Europa_Loader::loadClass($this->_dummyClass);
	}
}