<?php

/**
 * Interface for all suites and tests must implement.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart http://europaphp.org/license
 */
interface Testes_Testable
{
    /**
     * Runs all tests.
     * 
     * @return Testes_Testable
     */
    public function run();
    
    /**
     * Sets up the test.
     * 
     * @return void
     */
    public function setUp();
    
    /**
     * Tears the test down.
     * 
     * @return void
     */
    public function tearDown();
    
    /**
     * Returns the failed assertions.
     * 
     * @return array
     */
    public function assertions();
}