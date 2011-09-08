<?php

namespace Testes;

/**
 * Abstract test class that implements all methods for test suites and base class.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class TestAbstract implements TestInterface
{
    /**
     * The failed assertion list.
     * 
     * @var array
     */
    private $assertions = array();
    
    /**
     * The time it took for the test to run.
     * 
     * @var int
     */
    private $time = 0;
    
    /**
     * The amount of memory used during the test run.
     * 
     * @var int
     */
    private $memory = 0;
    
    /**
     * The time the test started.
     * 
     * @var int
     */
    private $starTime = 0;
    
    /**
     * The time the test ended.
     * 
     * @var int
     */
    private $stopTime = 0;
    
    /**
     * The memory used at the start of the test.
     * 
     * @var int
     */
    private $startMemory = 0;
    
    /**
     * The memory used at the end of the test.
     * 
     * @var int
     */
    private $stopMemory = 0;
    
    /**
     * The peak memory used during the test.
     * 
     * @var int
     */
    private $peakMemory = 0;
    
    /**
     * Sets up the test.
     * 
     * @return void
     */
    public function setUp()
    {
        
    }
    
    /**
     * Tears down the test.
     * 
     * @return void
     */
    public function tearDown()
    {
        
    }
    
    /**
     * Creates an assertion.
     * 
     * @param bool   $expression  The expression to test.
     * @param string $description The description of the assertion.
     * @param int    $code        A code if necessary.
     * 
     * @return \Testes\UnitTest\Test
     */
    public function assert($expression, $description, $code = Assertion::DEFAULT_CODE)
    {
        $this->assertions[] = new Assertion($expression, $description, $code);
        return $this;
    }
    
    /**
     * Logs the assertion and if it fails, a fatal assertion is thrown and the test exists.
     * 
     * @param bool   $expression  The expression to test.
     * @param string $description The description of the assertion.
     * @param int    $code        A code if necessary.
     * 
     * @return \Testes\UnitTest\Test
     */
    public function assertFatal($expression, $description, $code = Assertion::DEFAULT_CODE)
    {
        $this->assertions[] = new Assertion($expression, $description, $code);
        if (!$expression) {
            throw new FatalAssertion($description, $code);
        }
        return $this;
    }
    
    /**
     * Returns the number of milliseconds it took to run the tests.
     * 
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }
    
    /**
     * Returns the peak amount of memory that was used during the test.
     * 
     * @return int
     */
    public function getMemory()
    {
        return $this->memory;
    }
    
    /**
     * Returns all assertions in the order they were asserted.
     * 
     * @return array
     */
    public function getAssertions()
    {
        return $this->assertions;
    }
    
    /**
     * Returns the exceptions that were thrown in the suite.
     * 
     * @return array
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }
    
    /**
     * Adds an assertion to the test.
     * 
     * @param AssertionInterface $assertion The assertion to add.
     * 
     * @return TestAbstract
     */
    public function addAssertion(AssertionInterface $assertion)
    {
        $this->assertions[] = $assertion;
        return $this;
    }
    
    /**
     * Adds an array of assertions to the test.
     * 
     * @param array $assertions The assertions to add.
     * 
     * @return TestAbstract
     */
    public function addAssertions(array $assertions)
    {
        foreach ($assertions as $assertion) {
            $this->addAssertion($assertion);
        }
        return $this;
    }
    
    /**
     * Returns whether or not the test passed.
     * 
     * @return bool
     */
    public function failed()
    {
        return !$this->passed();
    }
    
    /**
     * Returns whether or not the test passed.
     * 
     * @return bool
     */
    public function passed()
    {
        foreach ($this->assertions as $assertion) {
            if ($assertion->failed()) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Returns the failed assertions.
     * 
     * @return array
     */
    public function getFailedAssertions()
    {
        $assertions = array();
        foreach ($this->assertions as $assertion) {
            if ($assertion->failed()) {
                $assertions[] = $assertion;
            }
        }
        return $assertions;
    }
    
    /**
     * Returns the passed assertions.
     * 
     * @return array
     */
    public function getPassedAssertions()
    {
        $assertions = array();
        foreach ($this->assertions as $assertion) {
            if ($assertion->passed()) {
                $assertions[] = $assertion;
            }
        }
        return $assertions;
    }
    
    /**
     * Adds an exception to the test.
     * 
     * @param \Exception $exception The exception to add.
     * 
     * @return TestAbstract
     */
    public function addException(\Exception $exception)
    {
        $this->exceptions[] = $exception;
        return $this;
    }
    
    /**
     * Adds an array of exceptions.
     * 
     * @param array $exceptions The exceptions to add.
     * 
     * @return TestAbstract
     */
    public function addExceptions(array $exceptions)
    {
        foreach ($exceptions as $e) {
            $this->addException($e);
        }
        return $this;
    }
    
    /**
     * Starts the timer on the test. The start time is recored.
     * 
     * @return TestAbstract
     */
    public function startTimer()
    {
        $this->startTime = microtime(true);
        return $this;
    }
    
    /**
     * Stops the timer on the test. The stop time and total time it took the test to run is recorded.
     * 
     * @return TestAbstract
     */
    public function stopTimer()
    {
        $this->stopTime = microtime(true);
        $this->time     = $this->stopTime - $this->startTime;
        return $this;
    }
    
    /**
     * Starts the memory counter on the test. The start memory is recorded.
     * 
     * @return TestAbstract
     */
    public function startMemoryCounter()
    {
        $this->startMemory = memory_get_usage();
        return $this;
    }
    
    /**
     * Stops the memory counter on the test. The stop memory, peak memory and total memory used during the test is
     * recorded.
     * 
     * @return TestAbstract
     */
    public function stopMemoryCounter()
    {
        $this->stopMemory = memory_get_usage();
        $this->peakMemory = memory_get_peak_usage();
        $this->memory     = $this->peakMemory - $this->startMemory;
        return $this;
    }
}
