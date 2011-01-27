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
    class Alpha extends \Europa\Validator
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
}