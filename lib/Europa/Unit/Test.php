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
abstract class Europa_Unit_Test extends Europa_Unit_Testable
{
	public function __construct()
	{
		$class = new ReflectionClass($this);
		foreach ($class->getMethods() as $method) {
			$method = $method->getName();
			if ($this->_isTestMethod($method)) {
				$this[] = $method;
			}
		}
	}
	
	public function offsetSet($offset, $test)
	{
		if (!$this->_isTestMethod($test)) {
			throw new Europa_Unit_Exception(
				'Invalid test added to ' . get_class($this) . '.',
				Europa_Unit_Exception::INVALID_TEST_METHOD
			);
		}
		if (is_null($offset)) {
			$offset = $this->count();
		}
		$this->_tests[] = $test;
	}
	
	public function run()
	{
		$this->setUp();
		foreach ($this as $test) {
			$result = $this->$test();
			if ($result === true) {
				$this->_passed[] = $test;
			} elseif ($result === false) {
				$this->_failed[] = $test;
			} else {
				$this->_incomplete[] = $test;
			}
		}
		$this->tearDown();
		return $this;
	}
	
	protected function _isTestMethod($method)
	{
		return substr($method, 0, 4) === 'test' && method_exists($this, $method);
	}
}