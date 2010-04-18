<?php

/**
 * A default form button.
 * 
 * @category Form Elements
 * @package  Europa_Form
 * @license  Copyright 2010  Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Button extends Europa_Form_Element_Input
{
	/**
	 * Renders the reset element.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$this->type = 'button';
		
		return parent::__toString();
	}
}