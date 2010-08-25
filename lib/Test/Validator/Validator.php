<?php

class Test_Validator_Validator extends Europa_Unit_Test
{
	public function testRequired()
	{
		$required = new Europa_Validator_Required;
		return $required->isValid(true)
		    && $required->isValid('something')
		    && !$required->isValid(null)
		    && !$required->isValid(false)
		    && !$required->isValid('')
		    && !$required->isValid(array());
	}
	
	public function testNumber()
	{
		$number = new Europa_Validator_Number;
		return $number->isValid('0')
		    && $number->isValid(0)
		    && !$number->isValid(null)
		    && !$number->isValid(false)
		    && !$number->isValid('something')
		    && !$number->isValid(array())
		    && !$number->isValid(true);
	}
	
	public function testNumberRange()
	{
		$range = new Europa_Validator_NumberRange(1, 10);
		return $range->isValid(1)
		    && $range->isValid(10);
	}
	
	public function testAlpha()
	{
		$alpha = new Europa_Validator_Alpha;
		return $alpha->isValid('something')
		    && !$alpha->isValid('s0m3th1ng');
	}
	
	public function testAlphaNumeric()
	{
		$alnum = new Europa_Validator_AlphaNumeric;
		return $alnum->isValid('s0m3th1ng')
		    && $alnum->isValid('000000000')
		    && $alnum->isValid('something')
		    && !$alnum->isValid('s o m e t h i n g')
		    && !$alnum->isValid('s-o-m-e-t_h_i_n_g');
	}
}