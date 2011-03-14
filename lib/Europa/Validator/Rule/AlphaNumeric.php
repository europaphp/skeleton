<?php

namespace Europa\Validator\Rule;
use Europa\Validator;

/**
 * Checks to see if the value is an alpha-numeric string.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class AlphaNumeric extends Validator
{
    /**
     * Checks to make sure the value is alpha-numeric
     * 
     * @param mixed $value The value to validate.
     * 
     * @return void
     */
    public function validate($value)
    {
        if (preg_match('/^[a-zA-Z0-9]*$/', $value)) {
            $this->pass();
        } else {
            $this->fail();
        }
        return $this;
    }
}