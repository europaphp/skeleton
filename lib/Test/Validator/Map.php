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
class Test_Validator_Map extends Europa_Unit_Test
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
		$this->_validator['name']['required'] = new Europa_Validator_Message(self::NAME_ERROR);
		$this->_validator['age']['range']     = new Europa_Validator_Message(self::AGE_ERROR);
		$this->_validator['dob']['required']  = new Europa_Validator_Message(self::DOB_ERROR);
	}
	
	/**
	 * Tests mapped data validation.
	 * 
	 * @return bool
	 */
	public function testValidation()
	{
		return $this->_validator->isValid($this->_data);
	}
	
	/**
	 * Tests mapped error messages.
	 * 
	 * @return bool
	 */
	public function testMessages()
	{
		return (string) $this->_validator['name']['required'] === self::NAME_ERROR
		    && (string) $this->_validator['age']['range']     === self::AGE_ERROR
		    && (string) $this->_validator['dob']['required']  === self::DOB_ERROR;
	}
}