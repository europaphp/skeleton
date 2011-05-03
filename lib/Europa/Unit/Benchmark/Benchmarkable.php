<?php

namespace Europa\Unit\Benchmark;
use Europa\Unit\Runable;

/**
 * Interface that all suites and tests must implement.
 * 
 * @category Benchmarking
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
interface Benchmarkable extends Runable
{
	/**
	 * Returns the results of the benchmark.
	 * 
	 * @return array
	 */
	public function results();
}