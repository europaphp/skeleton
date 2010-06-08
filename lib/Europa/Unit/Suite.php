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
abstract class Europa_Unit_Suite implements Europa_Unit_Testable
{
	/**
	 * Contains the tests to be run.
	 * 
	 * @var array
	 */
	protected $_tests = array();
	
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
	 * Gets run before all tests.
	 * 
	 * @return void
	 */
	public function setUp()
	{
		
	}
	
	/**
	 * Gets run after all tests.
	 * 
	 * @return void
	 */
	public function tearDown()
	{
		
	}
	
	/**
	 * Returns the name of the current test.
	 * 
	 * @return string
	 */
	public function getName()
	{
		return get_class($this);
	}
	
	/**
	 * Adds a test to the suite.
	 * 
	 * @param Europa_Unit_Testable $test The test/suite to add.
	 * @return Europa_Unit_Suite
	 */
	public function add(Europa_Unit_Testable $test)
	{
		$this->_tests[] = $test;
		return $this;
	}
	
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
		foreach ($this->_tests as $class) {
			// pre-test hook and running
			$class->setUp();
			$result = $class->run();
			
			// all forms of test are either test groups or just tests
			if ($class instanceof Europa_Unit_Suite) {
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