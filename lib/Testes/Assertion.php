<?php

/**
 * The base assertion class. Exctends the exception class so it can be 
 * thrown. However, it is recommended to throw a fatal assertion instead.
 * 
 * @category Assertions
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/
 */
class Testes_Assertion extends Testes_Exception
{
    /**
     * The test information that made the assertion.
     * 
     * @var array
     */
    protected $test;
    
    /**
     * Assertions are just exceptions. This constructs the parent exception
     * and gathers information about the test that made the assertion.
     * 
     * @param string $message The message to give the assertion.
     * @param int    $code    The code to give the assertion.
     * 
     * @return Testes_Assertion
     */
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
        $this->trace = $this->getTrace();
    }
    
    /**
     * Returns the test file.
     * 
     * @return string
     */
    public function getTestFile()
    {
        return $this->trace[0]['file'];
    }
    
    /**
     * Returns the line the assertion occurred on.
     * 
     * @return int
     */
    public function getTestLine()
    {
        return $this->trace[0]['line'];
    }
    
    /**
     * Returns the test class that the test method that made the
     * assertion was defined in.
     * 
     * @return string
     */
    public function getTestClass()
    {
        return $this->trace[1]['class'];
    }
    
    /**
     * Returns the test method that made the assertion.
     * 
     * @return string
     */
    public function getTestMethod()
    {
        return $this->trace[1]['function'];
    }
}