<?php

/**
 * Base class for a group of test classes. Since this class implements the
 * testable interface, there can be multiple levels of test groups and tests.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class Testes_Suite implements Testes_Testable, Iterator, Countable
{
    /**
     * Contains all failed assertions.
     * 
     * @var array
     */
    protected $assertions = array();
    
    /**
     * Contains the tests to be run.
     * 
     * @var array
     */
    protected $tests = array();
    
    /**
     * Constructs the test suite and adds all testable class instances.
     * 
     * @return Testes_Suite
     */
    final public function __construct()
    {
        // reflection for getting path and class information
        $self = new ReflectionClass($this);
        
        // get the path
        $path = $self->getFileName();
        $path = str_replace('.php', '', $path);
        
        // get the namespace
        $namespace = $self->getName();
        
        // load each file in the suite by convention
        foreach (new DirectoryIterator($path) as $file) {
            if (!$this->isValid($file)) {
                continue;
            }
            
            // add the test
            $class = str_replace('.php', '', $file->getBasename());
            $class = $namespace . '_' . $class;
            $this->addTest(new $class);
        }
    }
    
    /**
     * Runs all tests in the suite. Also handles tears down the suite and
     * failed test before re-throwing the exception.
     * 
     * @return Testes_Suite
     */
    final public function run()
    {
        $this->setUp();
        foreach ($this as $test) {
            $test->setUp();
            try {
                $test->run();
                $this->assertions = array_merge($this->assertions, $test->assertions());
            } catch (Testes_FatalAssertion $e) {
                $this->assertions[] = $e;
                $test->tearDown();
                $this->tearDown();
                return $this;
            } catch (Exception $e) {
                $test->tearDown();
                $this->tearDown();
                throw $e;
            }
            $test->tearDown();
        }
        $this->tearDown();
        return $this;
    }
    
    /**
     * Returns the name of the current test or test group.
     * 
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }
    
    /**
     * Set up event.
     * 
     * @return void
     */
    public function setUp()
    {
        
    }
    
    /**
     * Tear down event.
     * 
     * @return void
     */
    public function tearDown()
    {
        
    }
    
    /**
     * Returns the current test.
     * 
     * @return Testes_Testable
     */
    public function current()
    {
        return current($this->tests);
    }
    
    /**
     * Returns the key of the current test.
     * 
     * @return int
     */
    public function key()
    {
        return key($this->tests);
    }
    
    /**
     * Moves to the next test.
     * 
     * @return void
     */
    public function next()
    {
        next($this->tests);
    }
    
    /**
     * Resets the test iterator.
     * 
     * @return void
     */
    public function rewind()
    {
        reset($this->tests);
    }
    
    /**
     * Returns whether or not the iteration is still valid.
     * 
     * @return bool
     */
    public function valid()
    {
        return is_numeric($this->key());
    }
    
    /**
     * Returns the number of tests in the test/suite.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->tests);
    }
    
    /**
     * Returns the passed tests.
     * 
     * @return array
     */
    public function assertions()
    {
        return $this->assertions;
    }
    
    /**
     * Adds a test to run.
     * 
     * @param Testes_Testable $test The test to add.
     * 
     * @return Testes_Testable
     */
    protected function addTest(Testes_Testable $test)
    {
        $this->tests[] = $test;
        return $this;
    }
    
    /**
     * Tests whether or not the specified file is valid.
     * 
     * @param SplFileObject $file The file to check.
     * 
     * @return bool
     */
    protected function isValid(DirectoryIterator $file)
    {
        if ($file->isDot()) {
            return false;
        }
        
        if (strpos($file->getBasename(), '.') === 0) {
            return false;
        }
        
        return true;
    }
}