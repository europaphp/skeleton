<?php

namespace Testes;

/**
 * Interface that all suites and tests must implement.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface TestInterface
{
    /**
     * Sets up the test.
     * 
     * @return void
     */
    public function setUp();
    
    /**
     * Tears down the test.
     * 
     * @return void
     */
    public function tearDown();
    
    /**
     * Creates an assertion.
     * 
     * @param bool   $expression  The expression to test.
     * @param string $description The description of the assertion.
     * @param int    $code        A code if necessary.
     * 
     * @return \Testes\UnitTest\Test
     */
    public function assert($expression, $description, $code = Assertion::DEFAULT_CODE);
    
    /**
     * Logs the assertion and if it fails, a fatal assertion is thrown and the test exists.
     * 
     * @param bool   $expression  The expression to test.
     * @param string $description The description of the assertion.
     * @param int    $code        A code if necessary.
     * 
     * @return \Testes\UnitTest\Test
     */
    public function assertFatal($expression, $description, $code = Assertion::DEFAULT_CODE);
    
    /**
     * Returns the number of milliseconds it took to run the tests.
     * 
     * @return int
     */
    public function getTime();
    
    /**
     * Returns the peak amount of memory that was used during the test.
     * 
     * @return int
     */
    public function getMemory();
    
    /**
     * Returns all assertions in the order they were asserted.
     * 
     * @return array
     */
    public function getAssertions();
    
    /**
     * Returns the exceptions that were thrown in the suite.
     * 
     * @return array
     */
    public function getExceptions();
    
    /**
     * Adds an assertion to the test.
     * 
     * @param AssertionInterface $assertion The assertion to add.
     * 
     * @return TestAbstract
     */
    public function addAssertion(AssertionInterface $assertion);
    
    /**
     * Adds an array of assertions to the test.
     * 
     * @param array $assertions The assertions to add.
     * 
     * @return TestAbstract
     */
    public function addAssertions(array $assertions);
    
    /**
     * Returns the failed assertions.
     * 
     * @return array
     */
    public function getFailedAssertions();
    
    /**
     * Returns the passed assertions.
     * 
     * @return array
     */
    public function getPassedAssertions();
    
    /**
     * Adds an exception to the test.
     * 
     * @param \Exception $exception The exception to add.
     * 
     * @return TestAbstract
     */
    public function addException(\Exception $exception);
    
    /**
     * Adds an array of exceptions.
     * 
     * @param array $exceptions The exceptions to add.
     * 
     * @return TestAbstract
     */
    public function addExceptions(array $exceptions);
    
    /**
     * Starts the timer on the test. The start time is recored.
     * 
     * @return TestAbstract
     */
    public function startTimer();
    
    /**
     * Stops the timer on the test. The stop time and total time it took the test to run is recorded.
     * 
     * @return TestAbstract
     */
    public function stopTimer();
    
    /**
     * Starts the memory counter on the test. The start memory is recorded.
     * 
     * @return TestAbstract
     */
    public function startMemoryCounter();
    
    /**
     * Stops the memory counter on the test. The stop memory, peak memory and total memory used during the test is
     * recorded.
     * 
     * @return TestAbstract
     */
    public function stopMemoryCounter();
}
