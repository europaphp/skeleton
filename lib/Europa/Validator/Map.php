<?php

/**
 * Acts as a validation suite, but maps validators to input data.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Europa_Validator_Map extends Europa_Validator_Suite
{
    /**
     * Validates the suite based on the attached validators to the passed data.
     * 
     * @param mixed $data The data to validate.
     * 
     * @return bool
     */
    public function validate($data)
    {
        // must be an array
        if (!is_array($data)) {
            throw new Europa_Validator_Exception('The data being validated must be an array.');
        }
        
        // reset
        $this->failed = array();
        
        // validate
        foreach ($this as $id => $validator) {
            $value = isset($data[$id]) ? $data[$id] : null;
            $validator->validate($value);
        }
        
        // chain
        return $this;
    }
}