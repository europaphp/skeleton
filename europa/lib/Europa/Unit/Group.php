<?php

/**
 * Base class for a group of test classes. Since this class implements the
 * testable interface, there can be multiple levels of test groups and tests.
 * 
 * @category UnitTesting
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
abstract class Europa_Unit_Group implements Europa_Unit_Testable
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
	 * Returns the class names for the test classes. Since the classes are
	 * designed to be autoloaded, no paths are necessary.
	 * 
	 * @return array
	 */
	abstract public function getTests();
	
	/**
	 * Gets run before all tests.
	 * 
	 * @return void
	 */
	public function setUp()
	{}
	
	/**
	 * Gets run after all tests.
	 * 
	 * @return void
	 */
	public function tearDown()
	{}
	
	/**
	 * Runs all tests on each group.
	 * 
	 * @return mixed
	 */
	public function run()
	{
		// pre-test group hook
		$this->setUp();
		
		// run through each test/group and run them
		foreach ($this->getTests() as $class) {
			// class names are returned, so instantiate
			$class = new $class;
			
			// test must be testable
			if (!$class instanceof Europa_Unit_Testable) {
				throw new Europa_Unit_Exception(
					get_class($class) . 'must implement Europa_Unit_Testable'
				);
			}
			
			// pre-test hook and running
			$class->setUp();
			$result = $class->run();
			
			// all forms of test are either test groups or just tests
			if ($class instanceof Europa_Unit_Group) {
				$this->_passed     = array_merge($this->_passed, $class->getPassed());
				$this->_incomplete = array_merge($this->_incomplete, $class->getIncomplete());
				$this->_failed     = array_merge($this->_failed, $class->getFailed());
			} else {
				if ($result === true) {
					$this->_passed[] = $class;
				} elseif ($result === false) {
					$this->_failed[] = $class;
				} else {		
					$this->_incomplete[] = $class;
				}
			}
			
			// post-test hook
			$class->tearDown();
		}
		
		// post-test group hook
		$this->tearDown();
	}
	
	/**
	 * Returns the name of the test group.
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
	 * Returns the number of tests that passed. If tests weren't run yet, then
	 * 0 will be returned.
	 * 
	 * @return int
	 */
	public function countPassed()
	{
		return count($this->getPassed());
	}

	/**
	 * Returns the number of tests that were incomplete. If tests weren't run
	 * yet, then 0 will be returned.
	 * 
	 * @return int
	 */
	public function countIncomplete()
	{
		return count($this->getIncomplete());
	}

	/**
	 * Returns the number of tests that failed. If tests weren't run yet, then
	 * 0 will be returned.
	 * 
	 * @return int
	 */
	public function countFailed()
	{
		return count($this->getFailed());
	}
}