<?php

namespace Europa\Unit\Test;
use Europa\Unit\Runable;

/**
 * Interface that all suites and tests must implement.
 * 
 * @category UnitTesting
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface Testable extends Runable
{
    /**
     * Returns the failed assertions.
     * 
     * @return array
     */
    public function assertions();
}