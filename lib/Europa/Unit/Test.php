<?php

namespace Europa\Unit;
use Europa\Unit\Test\Suite;

/**
 * Basic class that will output tests results.
 * 
 * @category Testing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart
 */
abstract class Test extends Suite
{
    /**
     * Converts the test result to a string.
     * 
     * @return string
     */
    public function __toString()
    {
        $str = '';
        if (Output::isCli()) {
            $str .= Output::breaker();
        }
        
        if ($assertions = $this->assertions()) {
            foreach ($this->assertions() as $assertion) {
                $str .= $assertion->getTestClass()
                     .  '->'
                     .  $assertion->getTestMethod()
                     .  '() on line '
                     .  $assertion->getTestLine()
                     .  ': '
                     .  $assertion->getMessage()
                     .  Output::breaker();
            }
        } else {
            $str .= 'All tests passed!' . Output::breaker();
        }
        
        if (Output::isCli()) {
            $str .= Output::breaker();
        }
        
        return $str;
    }
}