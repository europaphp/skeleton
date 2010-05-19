<?php

/**
 * A default form input.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Input extends Europa_Form_Element
{
	/**
	 * Renders the reset element.
	 * 
	 * @return string
	 */
	public function toString()
	{
		if (!$this->getAttribute('type')) {
			$this->setAttribute('type', 'text');
		}
		return '<input ' . $this->getAttributeString() . ' />';
	}
}