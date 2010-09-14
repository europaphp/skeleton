<?php

/**
 * Validator for numbers.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Validator_Number extends Europa_Validator
{
    /**
     * Checks to make sure the specified value is a number.
     * 
     * @param mixed $value The value to validate.
     * @return Europa_Validator_Number
     */
    public function validate($value)
    {
        if (is_numeric($value)) {
            $this->pass();
        } else {
            $this->fail();
        }
        return $this;
    }
}