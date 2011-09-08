<?php

namespace Testes;

/**
 * The assertion class.
 * 
 * @category Assertions
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/
 */
class Assertion implements AssertionInterface
{
    /**
     * The default assertion code.
     * 
     * @var int
     */
    const DEFAULT_CODE = 0;
    
    /**
     * Assertions are just exceptions. This constructs the parent exception
     * and gathers information about the test that made the assertion.
     * 
     * @param bool   $expression The expression to evaluate.
     * @param string $message    The message to give the assertion.
     * @param int    $code       The code to give the assertion.
     * 
     * @return Testes_Assertion
     */
    public function __construct($expression, $message, $code = self::DEFAULT_CODE)
    {
        $this->trace      = debug_backtrace();
        $this->expression = $expression;
        $this->message    = $message;
        $this->code       = $code;
    }
    
    /**
     * Returns whether or not the assertion failed.
     * 
     * @return bool
     */
    public function failed()
    {
        return !$this->expression;
    }
    
    /**
     * Returns whether or not the assertion passed.
     * 
     * @return bool
     */
    public function passed()
    {
        return $this->expression;
    }
    
    /**
     * Returns the assertion message.
     * 
     * @return int
     */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * Returns the assertion code.
     * 
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * Returns the test file.
     * 
     * @return string
     */
    public function getTestFile()
    {
        return $this->trace[2]['file'];
    }
    
    /**
     * Returns the line the assertion occurred on. The trace index is different so we report the line the assertion was
     * made on in the test method.
     * 
     * @return int
     */
    public function getTestLine()
    {
        return $this->trace[1]['line'];
    }
    
    /**
     * Returns the test class that the test method that made the
     * assertion was defined in.
     * 
     * @return string
     */
    public function getTestClass()
    {
        return $this->trace[2]['class'];
    }
    
    /**
     * Returns the test method that made the assertion.
     * 
     * @return string
     */
    public function getTestMethod()
    {
        return $this->trace[2]['function'];
    }
}
