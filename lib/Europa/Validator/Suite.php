<?php

/**
 * Validates more than one validator against a value.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Validator
{
    class Suite implements Validatable, \ArrayAccess, \Iterator, \Countable
    {
        /**
         * Contains all validators attached to the suite.
         * 
         * @var array
         */
        protected $validators = array();
        
        /**
         * Validates the value against all validators in the suite.
         * 
         * @param mixed $value The value to validate.
         * 
         * @return \Europa\Validator\Suite
         */
        public function validate($value)
        {
            foreach ($this as $validator) {
                $validator->validate($value);
            }
            return $this;
        }
        
        /**
         * Throws a validation exception.
         * 
         * @param mixed  $value   The value to validate.
         * @param string $message The main validation message.
         * @param string $class   The exception class to throw.
         * 
         * @throws \Exception
         */
        public function validateAndThrow($value, $class = '\Europa\Validator\Exception')
        {
            $this->validate($value);
            $class = new $class;
            if (!$this->isValid()) {
                $class->fromTraversible($this->getMessages());
                throw $class;
            }
            return $this;
        }
        
        /**
         * Returns whether or not the last validation was successful.
         * 
         * @return bool
         */
        public function isValid()
        {
            foreach ($this as $index => $validator) {
                if (!$validator->isValid()) {
                    return false;
                }
            }
            return true;
        }
        
        /**
         * Fails validation.
         * 
         * @return \Europa\Validator\Validatable
         */
        public function fail()
        {
            foreach ($this as $validator) {
                $validator->fail();
            }
            return $this;
        }
        
        /**
         * Fails validation.
         * 
         * @return \Europa\Validator\Validatable
         */
        public function pass()
        {
            foreach ($this as $validator) {
                $validator->pass();
            }
            return $this;
        }
        
        /**
         * Returns all error messages.
         * 
         * @return array
         */
        public function getMessages()
        {
            $messages = array();
            foreach ($this as $validator) {
                $messages = array_merge($messages, $validator->getMessages());
            }
            return $messages;
        }
        
        /**
         * Returns the number of validators on the suite.
         * 
         * @return int
         */
        public function count()
        {
            return count($this->validators);
        }
        
        /**
         * Returns the current validator in the iteration.
         * 
         * @return \Europa\Validator\Validatable
         */
        public function current()
        {
            return current($this->validators);
        }
        
        /**
         * Returns the index of the current validator.
         * 
         * @return int|string
         */
        public function key()
        {
            return key($this->validators);
        }
        
        /**
         * Moves the internal pointer foward to the next validator in the suite.
         * 
         * @return void
         */
        public function next()
        {
            next($this->validators);
        }
        
        /**
         * Resets the internal pointer to the beginning of bound validators.
         * 
         * @return void
         */
        public function rewind()
        {
            reset($this->validators);
        }
        
        /**
         * Returns whether or not it is ok to continue iteration over the validators.
         * 
         * @return bool
         */
        public function valid()
        {
            return $this->offsetExists($this->key());
        }
        
        /**
         * Sets the specified validator.
         * 
         * @param int|string $index
         * @param \Europa\Validator\Validatable $value The validator to set.
         * 
         * @return void
         */
        public function offsetSet($index, $value)
        {
            $this->add($value, $index);
        }
        
        /**
         * Returns the specified validator.
         * 
         * @param int|string $index The index to get the validator from.
         * 
         * @return \Europa\Validator\Validatable|null
         */
        public function offsetGet($index)
        {
            if ($this->offsetExists($index)) {
                return $this->validators[$index];
            }
            return null;
        }
        
        /**
         * Returns whether or not the specified validator exists.
         * 
         * @param int|string $index The validator to check for.
         * 
         * @return bool
         */
        public function offsetExists($index)
        {
            return isset($this->validators[$index]);
        }
        
        /**
         * Unsets the specified validator if it exists.
         * 
         * @param int|string $index The offset to unset.
         * 
         * @return void
         */
        public function offsetUnset($index)
        {
            // remove the validator
            if ($this->offsetExists($index)) {
                unset($this->validators[$index]);
            }
            
            // remove any errors associated to the valdiator
            if (isset($this->_errors[$index])) {
                unset($this->_errors[$index]);
            }
        }
        
        /**
         * Adds the specified validator to the suite.
         * 
         * @param int|string                    $index     The index to add the validator at.
         * @param \Europa\Validator\Validatable $validator The validator to add.
         * 
         * @return \Europa\Validator\Validatable
         */
        protected function add(Europa_Validator_Validatable $validator, $index = null)
        {
            if (is_null($index)) {
                $index = $this->count();
            }
            $this->validators[$index] = $validator;
            return $this;
        }
    }
}