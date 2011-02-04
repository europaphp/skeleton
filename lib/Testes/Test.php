<?php

/**
 * Base test calss. The subclasses only need implement the run method.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class Testes_Test implements Testes_Testable
{
    /**
     * The default assertion code.
     * 
     * @var int
     */
    const DEFAULT_CODE = 0;
    
    /**
     * The failed assertion list.
     * 
     * @var array
     */
    protected $assertions = array();
    
    /**
     * Constructs the test and adds test methods.
     * 
     * @return Testes_Test
     */
    public function __construct()
    {
        $self = new ReflectionClass($this);
        foreach ($self->getMethods() as $method) {
            if (!$method->isPublic() || strpos($method->getName(), 'test') !== 0) {
                continue;
            }
            $this->tests[] = $method->getName();
        }
    }
    
    /**
     * Runs all test methods.
     * 
     * @return Testes_Test
     */
    public function run()
    {
        $this->setUp();
        foreach ($this->tests as $test) {
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
     * @return Testes_Test
     */
    public function assert($expression, $description, $code = self::DEFAULT_CODE)
    {
        if (!$expression) {
            $this->assertions[] = new Testes_Assertion($description, $code);
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
     * @return Testes_Test
     */
    public function assertFatal($expression, $description, $code = self::DEFAULT_CODE)
    {
        if (!$expression) {
            throw new Testes_FatalAssertion($description, $code);
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