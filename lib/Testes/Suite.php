<?php

namespace Testes;

/**
 * Base test class for test cases and test suites. Subclasses need only to implement setting up and tearing down if
 * required.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class Suite extends TestAbstract
{
    /**
     * The tests on this test suite.
     * 
     * @var array
     */
    private $tests = array();
    
    /**
     * Constructs the test and auto-detects the tests that should be run.
     * 
     * @return Suite
     */
    public function __construct()
    {
        $this->addTests($this->getTestInstances());
    }
    
	/**
     * Runs all tests in the suite.
     * 
     * @return Suite
     */
    public function run()
    {
        $this->setUp();
        $this->startMemoryCounter();
        $this->startTimer();
        foreach ($this->getTests() as $test) {
            try {
                $test->run();
                $this->addAssertions($test->getAssertions());
            } catch (\Exception $e) {
                $this->addException($e);
                $test->tearDown();
                $this->tearDown();
            }
        }
        $this->stopTimer();
        $this->stopMemoryCounter();
        $this->tearDown();
        return $this;
    }
    
    /**
     * Returns only classes that are valid unit tests classes from the current test directory and any subdirectories.
     * 
     * @return array
     */
    public function getTestClasses()
    {
        $self      = new \ReflectionClass($this);
        $path      = $self->getFileName();
        $path      = str_replace('.php', '', $path);
        $namespace = $self->getName();
        $classes   = array();
        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isDir() || strpos($file->getBasename(), '.') === 0) {
                continue;
            }
            $class      = str_replace('.php', '', $file->getBasename());
            $class      = $namespace . '\\' . $class;
            $class      = new \ReflectionClass($class);
            $interfaces = array();
            foreach ($self->getInterfaces() as $iface) {
                if ($iface->isUserDefined()) {
                    $userInterfaces[] = $iface->getName();
                }
            }
            
            foreach ($interfaces as $iface) {
                if (!$class->implementsInterface($iface)) {
                    continue 2;
                }
            }
            $classes[] = $class->getName();
        }
        return $classes;
    }
    
    /**
     * Returns an array of test instances.
     * 
     * @return array
     */
    public function getTestInstances()
    {
        $instances = array();
        foreach ($this->getTestClasses() as $class) {
            $instances[] = new $class;
        }
        return $instances;
    }
    
    /**
     * Adds a test to the suite. If a suite is being added, it adds that suite's tests recursively.
     * 
     * @param TestInterface $test The test to add.
     * 
     * @return Suite
     */
    public function addTest(TestInterface $test)
    {
        if ($test instanceof self) {
            $this->addTests($test->getTests());
        } else {
            $this->tests[] = $test;
        }
        return $this;
    }
    
    /**
     * Adds tests to the suite.
     * 
     * @param array $tests The tests to add.
     * 
     * @return Suite
     */
    public function addTests(array $tests)
    {
        foreach ($tests as $test) {
            $this->addTest($test);
        }
        return $this;
    }
    
    /**
     * Returns an array of test classes in this suite.
     * 
     * @return array
     */
    public function getTests()
    {
        return $this->tests;
    }
}
