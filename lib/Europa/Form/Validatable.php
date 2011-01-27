<?php

/**
 * Interface for form validation.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Form
{
    interface Validatable
    {
        /**
         * Validates the value(s) against the set validator.
         * 
         * @return Europa_Form_Validatable
         */
        public function validate();
        
        /**
         * Returns whether or not the last validation was successful.
         * 
         * @return bool
         */
        public function isValid();
        
        /**
         * Returns the messages if validation failed or an empty array.
         * 
         * @return array
         */
        public function getMessages();
    }
}