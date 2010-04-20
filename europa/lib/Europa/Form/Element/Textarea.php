<?php

/**
 * A default form textarea input.
 * 
 * @category Form
 * @package  Europa
 * @author   Trey Shugart
 * @license  (c) 2010 Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Textarea extends Europa_Form_Element
{
	/**
	 * Renders the textarea element.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return '<textarea '
		     . $this->getAttributeString()
		     . '>'
		     . $this->value
		     . '</textarea>';
	}
}