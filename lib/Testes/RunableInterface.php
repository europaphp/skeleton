<?php

namespace Testes;
use Countable;
use Testes\Assertion\AssertionInterface;
use Traversable;

interface RunableInterface extends Countable
{
    /**
     * Runs the tests.
     * 
     * @return void
     */
    public function run();
    
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
     * Starts the reporter.
     * 
     * @return Reporter
     */
    public function startBenchmark();
    
    /**
     * Stops the reporter.
     * 
     * @return Reporter
     */
    public function stopBenchmark();
    
    /**
     * Returns the number of milliseconds it took to run the tests.
     * 
     * @return int
     */
    public function getTime();
    
    /**
     * Returns the start time.
     * 
     * @return int
     */
    public function getStartTime();
    
    /**
     * Returns the start time.
     * 
     * @return int
     */
    public function getStopTime();
    
    /**
     * Returns the peak amount of memory that was used during the test.
     * 
     * @return int
     */
    public function getMemory();
    
    /**
     * Returns the start memory.
     * 
     * @return int
     */
    public function getStartMemory();
    
    /**
     * Returns the stop memory.
     * 
     * @return int
     */
    public function getStopMemory();
    
    /**
     * Returns an array of all assertions.
     * 
     * @return Set
     */
    public function getAssertions();
    
    /**
     * Returns an array of all exceptions.
     * 
     * @return Traversable
     */
    public function getExceptions();
}