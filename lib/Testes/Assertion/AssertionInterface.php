<?php

namespace Testes\Assertion;

/**
 * Assertion interface that makes sure an assertion can be added to a test.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
interface AssertionInterface
{
    /**
     * Returns whether or not the assertion failed.
     * 
     * @return bool
     */
    public function failed();
    
    /**
     * Returns whether or not the assertion passed.
     * 
     * @return bool
     */
    public function passed();
    
    /**
     * Returns the assertion message.
     * 
     * @return int
     */
    public function getMessage();
    
    /**
     * Returns the assertion code.
     * 
     * @return int
     */
    public function getCode();
    
    /**
     * Returns the test file.
     * 
     * @return string
     */
    public function getTestFile();
    
    /**
     * Returns the line the assertion occurred on. The trace index is different so we report the line the assertion was
     * made on in the test method.
     * 
     * @return int
     */
    public function getTestLine();
    
    /**
     * Returns the test class that the test method that made the
     * assertion was defined in.
     * 
     * @return string
     */
    public function getTestClass();
    
    /**
     * Returns the test method that made the assertion.
     * 
     * @return string
     */
    public function getTestMethod();
}