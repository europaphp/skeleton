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
abstract class Europa_Validator
{
	/**
	 * The message level associated to errors.
	 * 
	 * @var int
	 */
	const LEVEL_ERROR = 3;
	
	/**
	 * The message level associated to warnings.
	 * 
	 * @var int
	 */
	const LEVEL_WARNING = 2;
	
	/**
	 * The message level associated to successes.
	 * 
	 * @var int
	 */
	const LEVEL_SUCCESS = 0;
	
	/**
	 * The messages associated to the validator. Messages are organized into
	 * levels. The default levels are provided as LEVEL_ constants in this
	 * class.
	 * 
	 * @var string
	 */
	protected $_messages = array();
	
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
	 * @param string $msg The message to add.
	 * @param int $level The level to add the message to.
	 * @return Europa_Validator
	 */
	public function addMessage($message, $level = Europa_Validator::LEVEL_ERROR)
	{
		if (!is_array($this->_messages[$level]) {
			$this->_messages[$level] = array();
		}
		$this->_messages[$level][] = $message;
		return $this;
	}
	
	/**
	 * Returns a list of messages for the specified level.
	 * 
	 * @param int $level The error level to retrieve the messages for.
	 * @return array
	 */
	public function getMessages($level = Europa_Validator::LEVEL_ERROR)
	{
		if (isset$this->_messages[$level]) {
			return $this->_messages[$level];
		}
		return array();
	}
}