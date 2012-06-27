<?php

namespace Testes\Coverage;
use RuntimeException;

class Coverage
{
	public function __construct()
	{
		// ensure that XDEBUG is enabled
		if (!function_exists('xdebug_start_code_coverage')) {
			throw new RuntimeException('You must have the XDEBUG extension installed in order to analyze code coverage.');
		}

		// ensure that XDEBUG code coverage is enabled
		ini_set('xdebug.coverage_enable', 1);
	}

	public function start()
	{
		$this->stop();
		xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
		return $this;
	}
	
	public function pause()
	{
		xdebug_stop_code_coverage(false);
		return $this;
	}
	
	public function stop()
	{
		$analyzer = $this->analyze();
		xdebug_stop_code_coverage();
		return $analyzer;
	}

	public function analyze()
	{
		return new Analyzer(new CoverageResult(xdebug_get_code_coverage()));
	}
}
