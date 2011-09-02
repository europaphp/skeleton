<?php

namespace Europa\Validator\Rule;
use Europa\Validator;

/**
 * Validator that validates if a value is in a list of values.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <trey.shugart@gmail.com>
 * @author   Maxime Aoustin <max44410@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class InList extends Validator
{
	/**
	* The list of possible values
	*
	* @param Array
	*/
	private $values;
	
	
	/**
     * Sets array of possible values.
     * 
     * @param array $values Array of values.
     * 
     * @return \Europa\Validator\InList
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Checks to make sure the specified value is 
     * in the list of possible values.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return void
     */
    public function validate($value)
    {
        if (!in_array($value, $this->values)) {
            $this->fail();
        } else {
            $this->pass();
        }
        return $this;
    }
}