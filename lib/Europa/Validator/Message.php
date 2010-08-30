<?php

/**
 * Represents a validation message.
 * 
 * @category   Messages
 * @package    Europa
 * @subpackage Validator
 * @author     Trey Shugart <treshugart@gmail.com>
 * @copyright  (c) 2010 Trey Shugart
 * @link       http://europaphp.org/license
 */
class Europa_Validator_Message
{
	/**
	 * The message string.
	 * 
	 * @var int
	 */
	private $_message = null;
	
	/**
	 * Constructs a new error message.
	 * 
	 * @param string $message The message string.
	 * @return Europa_Validator_Message
	 */
	public function __construct($message = null)
	{
		$this->_message = $message;
	}
	
	/**
	 * Converts the message to a string.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->_message;
	}
}