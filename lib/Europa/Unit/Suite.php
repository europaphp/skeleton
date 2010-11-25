<?php

/**
 * Base class for a group of test classes. Since this class implements the
 * testable interface, there can be multiple levels of test groups and tests.
 * 
 * @category UnitTesting
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class Europa_Unit_Suite extends Europa_Unit_Testable
{
    /**
     * Constructs the test suite and adds all testable class instances.
     * 
     * @return Europa_Unit_Suite
     */
    public function __construct()
    {
        $name = get_class($this);
        $path = Europa_Loader::searchClass($name);
        $path = str_replace('.php', '', $path);
        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDir()) {
                continue;
            }
            $class = str_replace('.php', '', $file->getBasename());
            $class = get_class($this) . '_' . $class;
            if ($this->_isTestClass($class)) {
                $this[] = new $class;
            }
        }
    }
    
    /**
     * Runs all tests on each group.
     * 
     * @return mixed
     */
    public function run()
    {
        $this->setUp();
        foreach ($this as $test) {
            $test->run();
            $this->_passed     = array_merge($this->_passed, $test->getPassed());
            $this->_incomplete = array_merge($this->_incomplete, $test->getIncomplete());
            $this->_failed     = array_merge($this->_failed, $test->getFailed());
        }
        $this->tearDown();
    }
    
    /**
     * Adds the test to the suite.
     * 
     * @param int                  $offset The offset to add the test to.
     * @param Europa_Unit_Testable $test   The test to add.
     * 
     * @return Europa_Unit_Suite
     */
    public function offsetSet($offset, $test)
    {
        if (is_string($test)) {
            $test = new $test;
        }
        if (!$test instanceof Europa_Unit_Testable) {
            throw new Europa_Unit_Exception(
                'Test/suite being added to ' . get_class($this) . ' must extend Europa_Unit_Testable.',
                Europa_Unit_Exception::INVALID_TEST_CLASS
            );
        }
        if (is_null($offset)) {
            $offset = $this->count();
        }
        $this->_tests[] = $test;
    }
    
    /**
     * Returns whether or not the specified class is a valid test class.
     * 
     * @param mixed $class An instance or string representing the class to check.
     * 
     * @return bool
     */
    protected function isTestClass($class)
    {
        $reflect = new ReflectionClass($class);
        return !$reflect->isAbstract()
            && $reflect->isSublcassOf('Europa_Unit_Testable');
    }
}