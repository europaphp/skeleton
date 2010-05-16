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
abstract class Europa_Unit_Test implements Europa_Unit_Testable
{
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
	 * Returns the name of the test that was run.
	 * 
	 * @return string
	 */
	public function getName()
	{
		// default is the class name
		return get_class($this);
	}
}