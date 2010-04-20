<?php

/**
 * An abstract class for validator classes.
 * 
 * @category Validator
 * @package  Europa
 * @author   Trey Shugart
 * @license  (c) 2010 Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license
 */
abstract class Europa_Validator
{
	/**
	 * The error message associated to the validator.
	 * 
	 * @var string
	 */
	protected $_message = null;
	
	/**
	 * Performs validation and returns whether or not the value associated to
	 * the validator instance is valid or not.
	 * 
	 * @return bool
	 */
	abstract public function isValid($value);
	
	/**
	 * Adds a message to the validator for the indicated level.
	 * 
	 * @param string $msg   The message to add.
	 * @param int    $level The level to add the message to.
	 * 
	 * @return Europa_Validator
	 */
	public function addMessage($message)
	{
		$this->_message = $message;
		
		return $this;
	}
	
	/**
	 * Returns a list of messages for the specified level.
	 * 
	 * @param int $level The error level to retrieve the messages for.
	 * 
	 * @return array
	 */
	public function getMessages()
	{
		return $this->_message;
	}
}