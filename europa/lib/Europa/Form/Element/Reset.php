<?php

/**
 * A default form reset button.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Reset extends Europa_Form_Element_Button
{
	/**
	 * Renders the reset element.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$this->type = 'reset';

		if (!$this->value) {
			$this->value = 'Reset';
		}

		return parent::__toString();
	}
}