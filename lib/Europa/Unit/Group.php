<?php

abstract class Europa_Unit_Group
{
	protected $passed = array();
	
	protected $incomplete = array();
	
	protected $failed = array();
	
	/**
	 * Runs all test methods in the test group.
	 * 
	 * @return Europa_Unit
	 */
	public function run()
	{
		if (method_exists($this, 'setUp')) {
			$this->setUp();
		}
		
		foreach ($this->getTestMethods() as $method) {
			$res = $this->$method();
			
			if ($res === true) {
				$this->passed[] = $method;
			}
			elseif ($res === false) {
				$this->failed[] = $method;
			}
			else {
				$this->incomplete[] = $method;
			}
		}
		
		if (method_exists($this, 'tearDown')) {
			$this->tearDown();
		}
	}
	
	public function getTestMethods()
	{
		$class   = new ReflectionClass($this);
		$methods = array();
		
		foreach ($class->getMethods() as $index => $method) {
			$method = $method->getName();
			
			if (strpos($method, 'test') === 0) {
				$methods[] = $method;
			}
		}
		
		return $methods;
	}
	
	public function getName()
	{
		return get_class($this);
	}
	
	public function getPassed()
	{
		return $this->passed;
	}

	public function getIncomplete()
	{
		return $this->incomplete;
	}

	public function getFailed()
	{
		return $this->failed;
	}
	
	public function countPassed()
	{
		return count($this->passed);
	}
	
	public function countIncomplete()
	{
		return count($this->incomplete);
	}
	
	public function countFailed()
	{
		return count($this->failed);
	}

	public function countTests()
	{
		return count($this->getTestMethods());
	}
}