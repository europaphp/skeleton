<?php

namespace Europa\Validator\Rule;
use Europa\Validator;

/**
 * Validator that validates if a value is set.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Required extends Validator
{
    /**
     * Checks to make sure the specified value is set.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return void
     */
    public function validate($value)
    {
        if (empty($value) && $value !== 0 && $value !== 0.0 && $value !== '0') {
            $this->fail();
        } else {
            $this->pass();
        }
        return $this;
    }
}