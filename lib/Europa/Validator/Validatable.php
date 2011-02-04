<?php

/**
 * An interface for building custom validators.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Validator
{
    interface Validatable
    {
        /**
         * Performs validation on the specified value.
         * 
         * @param mixed $value The value to validate.
         * 
         * @return void
         */
        public function validate($value);
        
        /**
         * Tells whether the last validation was successful or not.
         * 
         * @return bool
         */
        public function isValid();
        
        /**
         * Returns the messages associated to the validatable object.
         * 
         * @return array
         */
        public function getMessages();
        
        /**
         * Fails validation.
         * 
         * @return void
         */
        public function fail();
        
        /**
         * Passes validation.
         * 
         * @return void
         */
        public function pass();
    }
}