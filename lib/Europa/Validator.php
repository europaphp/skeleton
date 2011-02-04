<?php

/**
 * A base validator class that can be extended by any validator.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa
{
    abstract class Validator implements Validator\Validatable
    {
        /**
         * Whether or not the last validation was successful. Defaults to true; innocent until
         * proven guilty.
         * 
         * @var bool
         */
        private $_isValid = true;
        
        /**
         * The messages associated to the validator.
         * 
         * @var array
         */
        private $_messages = array();
        
        /**
         * Adds a message to the validator.
         * 
         * @param string $message The message to add.
         * 
         * @return \Europa\Validator
         */
        public function addMessage($message)
        {
            $this->_messages[] = (string) $message;
            return $this;
        }
        
        /**
         * Returns all messages associated to the validator if the validation was not successful.
         * 
         * @return array
         */
        public function getMessages()
        {
            if (!$this->isValid()) {
                return $this->_messages;
            }
            return array();
        }
        
        /**
         * Returns whether or not the last validation was successful.
         * 
         * @return bool
         */
        public function isValid()
        {
            return $this->_isValid;
        }
        
        /**
         * Fails validation.
         * 
         * @return \Europa\Validator
         */
        public function fail()
        {
            $this->_isValid = false;
            return $this;
        }
        
        /**
         * Passes validation.
         * 
         * @return \Europa\Validator
         */
        public function pass()
        {
            $this->_isValid = true;
            return $this;
        }
    }
}