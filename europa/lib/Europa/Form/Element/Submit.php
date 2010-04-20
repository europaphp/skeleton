<?php

/**
 * A default form submit button.
 * 
 * @category Form
 * @package  Europa
 * @author   Trey Shugart
 * @license  (c) 2010 Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Submit extends Europa_Form_Element_Button
{
	/**
	 * Renders the reset element.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$this->type = 'submit';
		
		if (!$this->value) {
			$this->value = 'Submit';
		}
		
		return parent::__toString();
	}
}