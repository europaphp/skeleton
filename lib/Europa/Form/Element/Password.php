<?php

/**
 * A default form password input.
 * 
 * @category Form Elements
 * @package  Europa_Form
 * @license  Copyright 2010  Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Password extends Europa_Form_Element_Input
{
	/**
	 * Renders the password input element.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$this->type = 'password';
		
		return parent::__toString();
	}
}