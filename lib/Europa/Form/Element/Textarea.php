<?php

/**
 * A default form textarea input.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
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