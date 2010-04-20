<?php

/**
 * A default form input.
 * 
 * @category Form
 * @package  Europa
 * @author   Trey Shugart
 * @license  (c) 2010 Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Input extends Europa_Form_Element
{
	/**
	 * Renders the reset element.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		if (!$this->type) {
			$this->type = 'text';
		}
		
		return '<input ' . $this->getAttributeString() . ' />';
	}
}