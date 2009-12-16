<?php

class UnitTest_Europa_Loader extends Europa_Unit
{
	public function testLoadingAClassWithAnIncludePath()
	{
		// create a test file
		$testFile = $this->_createTestClass();
		
		// add the current directory as a load path
		Europa_Loader::addIncludePath(dirname(__FILE__), 'testLoadPath');
		
		// load the temporary test class file
		$loaded =  Europa_Loader::loadClass('TestClass');
		
		// remove the load path
		Europa_Loader::removeIncludePath('testLoadPath');
		
		// remove the test file
		unlink($testFile);
		
		return $loaded;
	}
	
	public function testAttemptingToLoadANonExistentClass()
	{
		return Europa_Loader::loadClass('___badCLassNameadjfsdajfsdfadsfi___') === false;
	}
	
	public function testLoadingAClassUsingAnInputPath()
	{
		// create a temporary file to load
		$testFile = $this->_createTestClass();
		
		// load it using the current path as the input path
		$loaded = Europa_Loader::loadClass('TestClass', dirname(__FILE__));
		
		// remove it
		unlink($testFile);
		
		// return whether it was loaded or not
		return $loaded;
	}
	
	public function testRemovingALoadPathByPath()
	{
		$path = Europa_Loader::addIncludePath(dirname(__FILE__));
		
		Europa_Loader::removeIncludePath($path);
		
		return strpos(get_include_path(), $path) === false;
	}
	
	
	
	private function _createTestClass()
	{
		$rand = md5(rand() . microtime() . rand());
		$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TestClass.php';
		
		file_put_contents($file, '<?php class testClass' . $rand . ' {}');
		
		return $file;
	}
}