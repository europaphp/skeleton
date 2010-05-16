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
interface Europa_Unit_Testable
{
	/**
	 * Returns the name of the current test or test group.
	 * 
	 * @return string
	 */
	public function getName();
	
	/**
	 * Runs all tests.
	 * 
	 * @return Europa_Unit_Testable
	 */
	public function run();
	
	/**
	 * Pre-testing hook.
	 * 
	 * @return void
	 */
	public function setUp();
	
	/** 
	 * Post-testing hook.
	 * 
	 * @return void
	 */
	public function tearDown();
}