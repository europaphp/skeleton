<?php

namespace Europa\Unit\Benchmark;
use Europa\Unit\SuiteAbstract;

/**
 * Interface that anything that is runable must implement.
 * 
 * @category Benchmarking
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Suite extends SuiteAbstract implements Benchmarkable
{
    /**
     * The results of the benchmark.
     * 
     * @return array
     */
    protected $results = array();

    /**
     * Runs all benchmarks in the suite.
     * 
     * @return \Testes\Benchmark\Suite
     */
    public function run()
    {
        // set up the test suite
        $this->setUp();

        // run each test
        foreach ($this->getClasses() as $bench) {
            // instantiate and run
            $bench = new $bench;
            $bench->setUp();
            $bench->run();
            $bench->tearDown();

            // add up the results
            $this->results = array_merge($this->results, $bench->results());
        }

        // tear down the suite
        $this->tearDown();

        return $this;
    }

    /**
     * Returns the results of the benchmark.
     * 
     * @return array
     */
    public function results()
    {
        return $this->results;
    }
}