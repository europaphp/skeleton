<?php

/**
 * Validator for numbers.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Validator
{
    class Number extends \Europa\Validator
    {
        /**
         * Checks to make sure the specified value is a number.
         * 
         * @param mixed $value The value to validate.
         * 
         * @return \Europa\Validator\Number
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
}