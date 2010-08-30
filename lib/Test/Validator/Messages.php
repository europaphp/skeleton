<?php

/**
 * Tests for validating Europa_Validator_Message
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Test_Validator_Messages extends Europa_Unit_Test
{
	/**
	 * A generic error message to test.
	 * 
	 * @var string
	 */
	const GENERIC = 'test generic message';
	
	/**
	 * Sets up the message test.
	 * 
	 * @return void
	 */
	public function setUp()
	{
		$this->_genericMessage = new Europa_Validator_Message(self::GENERIC);
	}
	
	/**
	 * Tests the return value to make sure the message can be converted to a string.
	 * 
	 * @return bool
	 */
	public function testGenericMessageToString()
	{
		return $this->_genericMessage->__toString() === self::GENERIC;
	}
}