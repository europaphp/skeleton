<?php

/**
 * An abstract class for validator classes.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Validator_Alpha extends Europa_Validator
{
    /**
     * Checks to make sure the specified value is set.
     * 
     * @param mixed $value The value to validate.
     * @return Europa_Validator_Alpha
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