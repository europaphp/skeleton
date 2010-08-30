<?php

/**
 * A base validator class that can be extended by any validator.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
abstract class Europa_Validator implements Europa_Validator_Validatable
{
	/**
	 * The messages associated to the validator.
	 * 
	 * @var array
	 */
	private $_messages = array();
	
	/**
	 * Adds a message to the validator.
	 * 
	 * @param string $message The message to add.
	 * @return Europa_Validator
	 */
	public function addMessage($message)
	{
		$this->_messages[] = (string) $message;
		return $this;
	}
	
	/**
	 * Returns all messages associated to the validator.
	 * 
	 * @return array
	 */
	public function getMessages()
	{
		return $this->_messages;
	}
}