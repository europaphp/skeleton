<?php

namespace Europa\Unit;
use Europa\Unit\Benchmark\Suite;

/**
 * Converts the output of a benchmark suite to a string.
 * 
 * @category Benchmarking
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Benchmark extends Suite
{
    /**
     * Converts the benchmark result to a string.
     * 
     * @return string
     */
    public function __toString()
    {
        $str = '';
        $br  = Output::breaker();
        $sp1 = Output::spacer(1);
        $sp2 = Output::spacer(2);
        $sp3 = Output::spacer(3);
        $sp4 = Output::spacer(4);

        if (!Output::isCli()) {
            $str .= '<pre>';
        } else {
            $str .= $br;
        }

        foreach ($this->results() as $suite => $benchmarks) {
        	$str .= $suite . $br;
        	foreach ($benchmarks as $benchmark => $result) {
        		$str .= $sp2 . $benchmark . $br
        		     .  $sp4 . 'memory' . $sp1 . ':' . $sp1 . round($result['memory'] / 1024 / 1024, 3) . ' MB' . $br
        		     .  $sp4 . 'time' . $sp3 . ':' . $sp1 . round($result['time'], 3) . ' seconds' . $br . $br;
        	}
        }

        if (!Output::isCli()) {
            $str .= '</pre>';
        }

        return $str . Output::breaker();
    }
}