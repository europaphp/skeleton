<?php

/**
 * A default form textarea input.
 * 
 * @category Form Elements
 * @package  Europa_Form
 * @license  Copyright 2010  Trey Shugart <treshugart@gmail.com>
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
		$attributes = '';
		foreach ($this as $attr => $value) {
			if ($attr === 'value') {
				continue;
			}
			$attributes = ' ' . $attr . '="' . $value . '"';
		}
		
		return '<textarea'
		     . $attributes
		     . '>'
		     . $this->value
		     . '</textarea>';
	}
}