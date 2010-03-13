<?php

abstract class Europa_Unit_All
{
	protected $groups = array();
	
	/**
	 * Returns the paths and class names for the test classes.
	 * 
	 * Since Europa Unit is designed to be run from the command line, paths
	 * to the test classes are given so they can be included on the fly.
	 * 
	 * An array representing [class file path] => [class name] should be
	 * returned.
	 * 
	 * @return array
	 */
	abstract public function getTestClasses();
	
	public function __construct()
	{
		foreach ($this->getTestClasses() as $className) {
			$this->groups[] = new $className;
		}
	}
	
	public function run()
	{
		if (method_exists($this, 'setUp')) {
			$this->setUp();
		}
		
		foreach ($this->groups as $class) {
			$class->run();
		}
		
		if (method_exists($this, 'tearDown')) {
			$this->tearDown();
		}
		
		return $this;
	}
	
	public function getGroups()
	{
		return $this->groups;
	}
	
	public function countGroups()
	{
		return count($this->getTestClasses());
	}
}