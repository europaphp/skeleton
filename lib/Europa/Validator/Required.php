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
class Europa_Validator_Required extends Europa_Validator
{
    /**
     * Checks to make sure the specified value is set.
     * 
     * @param mixed $value The value to validate.
     * @return Europa_Validator_Required
     */
    public function validate($value)
    {
        if (empty($value)) {
            $this->fail();
        } else {
            $this->pass();
        }
        return $this;
    }
}