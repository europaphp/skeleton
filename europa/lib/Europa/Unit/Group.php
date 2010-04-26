<?php

/**
 * Base class for a test group.
 * 
 * By default all methods prefixed with 'test' are considered test methods and
 * will be ran and evaluated.
 * 
 * Both a setUp and a tearDown method is available and works as in other unit
 * test frameworks. Europa_Unit_Group->setUp() gets run after __construct(),
 * but before any tests are run. Europa_Unit_Group->tearDown() gets run after
 * the tests are run.
 * 
 * Tests that return true, pass; false, failed; and anything that returns
 * otherwise is considered incomplete.
 * 
 * @category UnitTesting
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
abstract class Europa_Unit_Group
{
	/**
	 * Contains all test names that passed.
	 * 
	 * @var array
	 */
	protected $_passed = array();
	
	/**
	 * Contains all test names that are incomplete.
	 * 
	 * @var array
	 */
	protected $_incomplete = array();
	
	/**
	 * Contains all test names that failed.
	 * 
	 * @var array
	 */
	protected $_failed = array();
	
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
				$this->_passed[] = $method;
			} elseif ($res === false) {
				$this->_failed[] = $method;
			} else {
				$this->_incomplete[] = $method;
			}
		}
		
		if (method_exists($this, 'tearDown')) {
			$this->tearDown();
		}
	}
	
	/**
	 * Retrieves all methods from the test group class and filters them
	 * returning only those which are valid test methods.
	 * 
	 * @return array
	 */
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
	
	/**
	 * Returns the name of the test group. Defaults to the class' name.
	 * 
	 * @return string
	 */
	public function getName()
	{
		return get_class($this);
	}
	
	/**
	 * Returns all test names that passed.
	 * 
	 * @return array
	 */
	public function getPassed()
	{
		return $this->_passed;
	}
	
	/**
	 * Returns all test names that were incomplete.
	 * 
	 * @return array
	 */
	public function getIncomplete()
	{
		return $this->_incomplete;
	}
	
	/**
	 * Returns all test names that failed.
	 * 
	 * @return array
	 */
	public function getFailed()
	{
		return $this->_failed;
	}
	
	/**
	 * Returns the number of tests that passed.
	 * 
	 * @return int
	 */
	public function countPassed()
	{
		return count($this->_passed);
	}
	
	/**
	 * Returns the number of test that were incomplete.
	 * 
	 * @return int
	 */
	public function countIncomplete()
	{
		return count($this->_incomplete);
	}
	
	/**
	 * Returns the number of tests that failed.
	 * 
	 * @return int
	 */
	public function countFailed()
	{
		return count(``);
	}
	
	/**
	 * Returns the total number of tests.
	 * 
	 * @return int
	 */
	public function countTotal()
	{
		return count($this->getTestMethods());
	}
}