<?php

/**
 * An abstract class for validator classes.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Validator
{
    class Required extends \Europa\Validator
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
            if (empty($value)) {
                $this->fail();
            } else {
                $this->pass();
            }
            return $this;
        }
    }
}