<?php

/**
 * Base class for a test group macro.
 * 
 * By extending this class and providing it with test classes, it will run
 * all tests methods in each test class and report on the results.
 * 
 * @category UnitTesting
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
abstract class Europa_Unit_All
{
	/**
	 * Holds references to all group class instances.
	 * 
	 * @var array
	 */
	protected $_groups = array();
	
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
	
	/**
	 * Constructs the controlling class and sets group class references.
	 * 
	 * @return Europa_Unit_All
	 */
	public function __construct()
	{
		foreach ($this->getTestClasses() as $className) {
			$this->_groups[] = new $className;
		}
	}
	
	/**
	 * Runs all tests on each group.
	 * 
	 * @return Europa_Unit_All
	 */
	public function run()
	{
		if (method_exists($this, 'setUp')) {
			$this->setUp();
		}
		
		foreach ($this->_groups as $class) {
			$class->run();
		}
		
		if (method_exists($this, 'tearDown')) {
			$this->tearDown();
		}
		
		return $this;
	}
	
	/**
	 * Returns a list of associated group references.
	 * 
	 * @return array
	 */
	public function getGroups()
	{
		return $this->_groups;
	}
	
	/**
	 * Counts the number of associated group references.
	 * 
	 * @return int
	 */
	public function countGroups()
	{
		return count($this->getTestClasses());
	}
}