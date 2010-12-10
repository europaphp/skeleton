<?php

/**
 * Tests for validating Europa_Validator.
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Test_Validator_Validator extends Europa_Unit_Test
{
	/**
	 * Tests the required validator.
	 * 
	 * @return bool
	 */
	public function testRequired()
	{
		$required = new Europa_Validator_Required;
		return $required->validate(true)->isValid()
		    && $required->validate('something')->isValid()
		    && !$required->validate(null)->isValid()
		    && !$required->validate(false)->isValid()
		    && !$required->validate('')->isValid()
		    && !$required->validate(array())->isValid();
	}
	
	/**
	 * Tests the number validator.
	 * 
	 * @return bool
	 */
	public function testNumber()
	{
		$number = new Europa_Validator_Number;
		return $number->validate('0')->isValid()
		    && $number->validate(0)->isValid()
		    && !$number->validate(null)->isValid()
		    && !$number->validate(false)->isValid()
		    && !$number->validate('something')->isValid()
		    && !$number->validate(array())->isValid()
		    && !$number->validate(true)->isValid();
	}
	
	/**
	 * Tests the number range validator.
	 * 
	 * @return bool
	 */
	public function testNumberRange()
	{
		$range = new Europa_Validator_NumberRange(1, 10);
		return $range->validate(1)->isValid()
		    && $range->validate(10)->isValid();
	}
	
	/**
	 * Tests the alpha character validator.
	 * 
	 * @return bool
	 */
	public function testAlpha()
	{
		$alpha = new Europa_Validator_Alpha;
		return $alpha->validate('something')->isValid()
		    && !$alpha->validate('s0m3th1ng')->isValid();
	}
	
	/**
	 * Tests the alpha-numeric character validator.
	 * 
	 * @return bool
	 */
	public function testAlphaNumeric()
	{
		$alnum = new Europa_Validator_AlphaNumeric;
		return $alnum->validate('s0m3th1ng')->isValid()
		    && $alnum->validate('000000000')->isValid()
		    && $alnum->validate('something')->isValid()
		    && !$alnum->validate('s o m e t h i n g')->isValid()
		    && !$alnum->validate('s-o-m-e-t_h_i_n_g')->isValid();
	}
}