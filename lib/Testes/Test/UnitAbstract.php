<?php

namespace Testes\Test;
use ArrayIterator;
use Testes\Assertion\Assertion;
use Testes\Assertion\Set;
use Testes\RunableAbstract;

/**
 * Abstract test class that implements all methods for test suites and base class.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class UnitAbstract extends RunableAbstract implements TestInterface
{
    /**
     * The test method names.
     * 
     * @var array
     */
    private $methods;
    
    /**
     * The assertions made on a test.
     * 
     * @var array
     */
    private $assertions = array();
    
    /**
     * Any exceptions thrown during the running of the tests.
     * 
     * @var array
     */
    private $exceptions = array();
    
    /**
     * Constructs a new abstract unit test.
     * 
     * @return UnitAbstract
     */
    public function __construct()
    {
        $this->methods    = $this->getMethods();
        $this->assertions = new Set;
        $this->exceptions = new ArrayIterator;
    }
    
    /**
     * Runs all test methods.
     * 
     * @return Test
     */
    public function run()
    {
        $this->setUp();
        foreach ($this->methods as $method) {
            try {
                $this->$method();
            } catch (Exception $e) {
                $this->exceptions[] = $e;
            }
        }
        $this->tearDown();
        return $this;
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
    public function assert($expression, $description = null, $code = Assertion::DEFAULT_CODE)
    {
        $this->assertions->add(new Assertion($expression, $description, $code));
        return $this;
    }
    
    /**
     * Returns all assertions made in the test.
     * 
     * @return Set
     */
    public function getAssertions()
    {
        return $this->assertions;
    }
    
    /**
     * Returns the exceptions thrown during the tests.
     * 
     * @return ArrayIterator
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }
    
    /**
     * Returns the number of test methods in the test.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->methods);
    }
    
    /**
     * Returns all public methods that are valid test methods.
     * 
     * @return array
     */
    private function getMethods()
    {
        $exclude = array();
        $include = array();
        $self    = new \ReflectionClass($this);
        
        // exclude any methods from the interfaces
        foreach ($self->getInterfaces() as $interface) {
            foreach ($interface->getMethods() as $method) {
                $exclude[] = $method->getName();
            }
        }
        
        // exclude any methods from the traits
        foreach ($self->getTraits() as $trait) {
            foreach ($trait->getMethods() as $method) {
                $exclude[] = $method->getName();
            }
        }
        
        // exclude methods
        foreach ($self->getMethods() as $method) {
            if (!$method->isPublic()) {
                continue;
            }
            
            // make sure it was delcared by the test class
            if ($method->getDeclaringClass()->getName() !== get_class($this)) {
                continue;
            }
    
            // exclude particular methods
            $method = $method->getName();
            if (in_array($method, $exclude)) {
                continue;
            }
            $include[] = $method;
        }
        
        // make sure no duplicates are returned
        return array_unique($include);
    }
}
