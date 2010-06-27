<?php

/**
 * Interface for determining if a test or test group is testable.
 * 
 * @category UnitTesting
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
abstract class Europa_Unit_Testable implements Iterator, ArrayAccess, Countable
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
	 * Contains the tests to be run.
	 * 
	 * @var array
	 */
	protected $_tests = array();
	
	/**
	 * Runs all tests.
	 * 
	 * @return Europa_Unit_Testable
	 */
	abstract public function run();
	
	/**
	 * Returns the name of the current test or test group.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return get_class($this);
	}
	
	public function setUp()
	{
		
	}
	
	public function tearDown()
	{
		
	}
	
	public function current()
	{
		return current($this->_tests);
	}
	
	public function key()
	{
		return key($this->_tests);
	}
	
	public function next()
	{
		next($this->_tests);
	}
	
	public function rewind()
	{
		reset($this->_tests);
	}
	
	public function valid()
	{
		return is_numeric($this->key());
	}
	
	public function offsetGet($offset)
	{
		if (isset($this->_tests[$offset])) {
			return $this->_tests[$offset];
		}
		return null;
	}
	
	public function offsetExists($offset)
	{
		return isset($this->_tests[$offset]);
	}
	
	public function offsetUnset($offset)
	{
		if (isset($this->_tests[$offset])) {
			unset($this->_tests[$offset]);
		}
	}
	
	public function count()
	{
		return count($this->_tests);
	}
	
	/**
	 * Returns whether or not the test was valid.
	 * 
	 * @return bool
	 */
	public function isValid()
	{
		return $this->countFailed() === 0;
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