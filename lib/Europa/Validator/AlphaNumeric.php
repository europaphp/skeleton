<?php

/**
 * An abstract class for validator classes.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Validator
{
    class AlphaNumeric extends \Europa\Validator
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
}