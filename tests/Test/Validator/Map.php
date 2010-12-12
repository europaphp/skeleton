<?php

/**
 * Tests for validating Europa_Validator_Map
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Test_Validator_Map extends Testes_Test
{
	/**
	 * The name error message.
	 * 
	 * @var string
	 */
	const NAME_ERROR = 'Please enter your name.';
	
	/**
	 * The name error message.
	 * 
	 * @var string
	 */
	const AGE_ERROR = 'You must be between 18 and 25.';
	
	/**
	 * The name error message.
	 * 
	 * @var string
	 */
	const DOB_ERROR = 'Please enter your date of birth.';
	
	/**
	 * The test data to be validated.
	 * 
	 * @var array
	 */
	private $_data = array();
	
	/**
	 * The validator map doing the validation.
	 * 
	 * @var Europa_Validator_Map
	 */
	private $_validator;
	
	/**
	 * Sets up the validator test.
	 * 
	 * @return void
	 */
	public function setUp()
	{
		// validation data
		$this->_data = array(
			'name' => 'Trey Shugart',
			'age'  => 18,
			'dob'  => '1983-01-02'
		);
		
		// create a validation map
		$this->_validator = new Europa_Validator_Map;
		$this->_validator['name'] = new Europa_Validator_Required;
		$this->_validator['age']  = new Europa_Validator_NumberRange(18, 25);
		$this->_validator['dob']  = new Europa_Validator_Required;
		
		// and add error messages
		$this->_validator['name']->addMessage(self::NAME_ERROR);
		$this->_validator['age']->addMessage(self::AGE_ERROR);
		$this->_validator['dob']->addMessage(self::DOB_ERROR);
	}
	
	/**
	 * Tests mapped data validation.
	 * 
	 * @return bool
	 */
	public function testValidation()
	{
		$this->assert(
		    $this->_validator->validate($this->_data)->isValid(),
		    'Validation failing.'
		);
	}
}