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
abstract class Europa_Validator implements Europa_Validator_Validatable
{
	/**
	 * The messages associated to the validator. Messages are organized into
	 * levels. The default levels are provided as LEVEL_ constants in this
	 * class.
	 * 
	 * @var string
	 */
	protected $_messages = array();
	
	/**
	 * Adds a message to the validator for the indicated level.
	 * 
	 * @param string $msg The message to add.
	 * @param int $level The level to add the message to.
	 * @return Europa_Validator
	 */
	public function addErrorMessage($message)
	{
		$this->_messages[] = $message;
		return $this;
	}
	
	/**
	 * Returns a list of messages for the specified level.
	 * 
	 * @param int $level The error level to retrieve the messages for.
	 * @return array
	 */
	public function getErrorMessages()
	{
		return $this->_messages;
	}
}