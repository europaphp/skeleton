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
	 * The error type.
	 * 
	 * @var int
	 */
	const ERROR = -1;
	
	/**
	 * The warning type.
	 * 
	 * @var int
	 */
	const WARNING = 0;
	
	/**
	 * The success type.
	 * 
	 * @var int
	 */
	const SUCCESS = 1;
	
	/**
	 * The message string.
	 * 
	 * @var int
	 */
	private $_message = null;
	
	/**
	 * The message type.
	 * 
	 * @var int
	 */
	private $_type;
	
	/**
	 * Constructs a new error message.
	 * 
	 * @param string $message The message string.
	 * @param string $type The message type.
	 * @return Europa_Validator_Message
	 */
	public function __construct($message = null, $type = self::ERROR)
	{
		$this->setMessage($message)->setType($type);
	}
	
	/**
	 * Converts the message to a string.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->getMessage();
	}
	
	/**
	 * Sets the message.
	 * 
	 * @param string $message The message to set.
	 * @return Europa_Validator_Message
	 */
	public function setMessage($message)
	{
		$this->_message = $message;
		return $this;
	}
	
	/**
	 * Returns the message.
	 * 
	 * @return string
	 */
	public function getMessage()
	{
		return $this->_message;
	}
	
	/**
	 * Sets the message type.
	 * 
	 * @param int $type The message type.
	 * @return Europa_Validator_Message
	 */
	public function setType($type)
	{
		$this->_type = $type;
		return $this;
	}
	
	/**
	 * Returns the message type.
	 * 
	 * @return int
	 */
	public function getType()
	{
		return $this->_type;
	}
}