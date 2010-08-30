<?php

abstract class Europa_Validator implements Europa_Validator_Validatable
{
	private $_messages = array();
	
	public function addMessage(Europa_Validator_Message $message)
	{
		$this->_messages[] = $message;
		return $this;
	}
	
	public function getMessages()
	{
		return $this->_messages;
	}
}