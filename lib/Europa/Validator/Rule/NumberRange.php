<?php

namespace Europa\Validator\Rule;
use Europa\Validator;

/**
 * Validator that checks to see if the specified value is in the valid number range.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class NumberRange extends Validator
{
    /**
     * The minimum value.
     * 
     * @param float
     */
    private $_min;
    
    /**
     * The maximum value.
     * 
     * @param float
     */
    private $_max;
    
    /**
     * Sets the number range to validate.
     * 
     * @param mixed $min The minimum value.
     * @param mixed $max The maximum value.
     * 
     * @return \Europa\Validator\NumberRange
     */
    public function __construct($min, $max)
    {
        $this->_min = (float) $min;
        $this->_max = (float) $max;
    }
    
    /**
     * Checks to make sure the specified value is set.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return \Europa\Validator\NumberRange
     */
    public function validate($value)
    {
        if (!is_numeric($value)) {
            if (is_string($value)) {
                $value = strlen($value);
            } else {
                $this->fail();
                return $this;
            }
        }
        
        $value = (float) $value;
        if ($value >= $this->_min && $value <= $this->_max) {
            $this->pass();
        } else {
            $this->fail();
        }
        return $this;
    }
}