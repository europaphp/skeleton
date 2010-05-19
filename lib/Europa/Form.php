<?php

/**
 * The main form class which is also an element list.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
abstract class Europa_Form extends Europa_Form_ElementList
{
	/**
	 * Renders the form and all of its elements and element lists.
	 * 
	 * @return string
	 */
	public function toString()
	{
		// build default form structure
		return '<form' 
		     . $this->getAttributeString()
		     . '>'
		     . parent::toString()
		     . '</form>';
	}
}