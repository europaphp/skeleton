<?php

/**
 * An abstract class for validator classes.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Validator_NumberRange extends Europa_Validator
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
	 * @param mixed $min The mininum value.
	 * @param mixed $max The maximum value.
	 * @return Viomedia_Validator_NumberRange
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
	 * @return Europa_Validator_NumberRange
	 */
	public function validate($value)
	{
		if (!is_numeric($value)) {
			$this->fail();
			return $this;
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