<?php

class Test_Validator_Suite extends Europa_Unit_Test
{
	public function testFailAllValidators()
	{
		$suite = new Europa_Validator_Suite;
		$suite[] = new Europa_Validator_Required;
		$suite[] = new Europa_Validator_Number;
		return $suite->isValid(null) === false;
	}
	
	public function testPassAllValidators()
	{
		$suite = new Europa_Validator_Suite;
		$suite[] = new Europa_Validator_Required;
		$suite[] = new Europa_Validator_Number;
		return $suite->isValid('1') === true;
	}
	
	public function testPassOneValidator()
	{
		$suite = new Europa_Validator_Suite;
		$suite[] = new Europa_Validator_Required;
		$suite[] = new Europa_Validator_Number;
		return $suite->isValid('something') === false;
	}
}