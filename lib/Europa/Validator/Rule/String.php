<?php

namespace Europa\Validator\Rule;
use Europa\Validator;

/**
 * Checks to see if the value is a string.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class String extends Validator
{
    /**
     * Checks to make sure the specified value is a string.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return \Europa\Validator\String
     */
    public function validate($value)
    {
        if (is_string($value)) {
            $this->pass();
        } else {
            $this->fail();
        }
        return $this;
    }
}