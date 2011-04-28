<?php

namespace Europa\Unit\Test;
use Europa\Unit\SuiteAbstract;

/**
 * Interface that anything that is runable must implement.
 * 
 * @category UnitTesting
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Suite extends SuiteAbstract implements Testable
{
	/**
	 * The assertions thrown when running the tests.
	 * 
	 * @var array
	 */
	protected $assertions = array();
    
    /**
     * Runs all tests in the suite. Also handles tears down the suite and
     * failed test before re-throwing the exception.
     * 
     * @return Suite
     */
    public function run()
    {
        // set up the test suite
        $this->setUp();

        // run each test
        foreach ($this->getClasses() as $test) {
            $test = new $test;
            
            // depending on what is asserted, we hadnle it differently
            try {
                $test->run();
                $this->assertions = array_merge($this->assertions, $test->assertions());
            } catch (FatalAssertion $e) {
                $this->assertions[] = $e;
                $test->tearDown();
                $this->tearDown();
                return $this;
            } catch (\Exception $e) {
                $test->tearDown();
                $this->tearDown();
                throw $e;
            }
        }

        // tear down the suite
        $this->tearDown();

        return $this;
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
}