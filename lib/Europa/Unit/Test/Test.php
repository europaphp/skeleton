<?php

namespace Europa\Unit\Test;
use Europa\Unit\TestAbstract;

/**
 * Base test calss. The subclasses only need implement the run method.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class Test extends TestAbstract implements Testable
{
    /**
     * The default assertion code.
     * 
     * @var int
     */
    const DEFAULT_CODE = 0;

    /**
     * The test method prefix.
     * 
     * @var string
     */
    const PREFIX = 'test';
    
    /**
     * The failed assertion list.
     * 
     * @var array
     */
    protected $assertions = array();
    
    /**
     * Runs all test methods.
     * 
     * @return \Testes\UnitTest\Test
     */
    public function run()
    {
        $this->setUp();
        foreach ($this->getMethods() as $test) {
            $this->$test();
        }
        $this->tearDown();
        return $this;
    }
    
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
     * @param bool   $expression
     * @param string $description
     * @param int    $code
     * 
     * @return \Testes\UnitTest\Test
     */
    public function assert($expression, $description, $code = self::DEFAULT_CODE)
    {
        if (!$expression) {
            $this->assertions[] = new Assertion($description, $code);
        }
        return $this;
    }
    
    /**
     * Creates an assertion.
     * 
     * @param bool   $expression
     * @param string $description
     * @param int    $code
     * 
     * @return \Testes\UnitTest\Test
     */
    public function assertFatal($expression, $description, $code = self::DEFAULT_CODE)
    {
        if (!$expression) {
            throw new FatalAssertion($description, $code);
        }
        return $this;
    }
    
    /**
     * Returns the failed assertions.
     * 
     * @return array
     */
    public function assertions()
    {
        return $this->assertions;
    }
}