<?php

/**
 * Acts as a validation suite, but maps validators to input data.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Validator
{
    class Map extends Suite
    {
        /**
         * Validates the suite based on the attached validators to the passed data.
         * 
         * @param mixed $data The data to validate.
         * 
         * @return void
         */
        public function validate($data)
        {
            // must be an array
            if (!is_array($data)) {
                throw new Exception('The data being validated must be an array.');
            }
            
            // reset
            $this->failed = array();
            
            // validate
            foreach ($this as $id => $validator) {
                $value = isset($data[$id]) ? $data[$id] : null;
                $validator->validate($value);
            }
            
            return $this;
        }
    }
}