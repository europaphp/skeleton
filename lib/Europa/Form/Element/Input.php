<?php

/**
 * A default form input.
 * 
 * @category Form Elements
 * @package  Europa_Form
 * @license  Copyright 2010  Trey Shugart <treshugart@gmail.com>
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
		
		$str = '<input';
		
		foreach ($this as $name => $value) {
			$str .= ' ' . $name . '="' . $value . '"';
		}
		
		return $str . ' />';
	}
}