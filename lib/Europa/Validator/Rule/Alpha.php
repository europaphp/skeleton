<?php

namespace Europa\Validator\Rule;
use Europa\Validator;

/**
 * Checks to see if the value is only alpha characters.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Alpha extends Validator
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
        if (preg_match('/^[a-zA-Z]*$/', $value)) {
            $this->pass();
        } else {
            $this->fail();
        }
        return $this;
    }
}