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
class Europa_Form extends Europa_Form_ElementList
{
	/**
	 * Renders the form and all of its elements and element lists.
	 * 
	 * @return string
	 */
	public function toString()
	{
		if (!$this->getAttribute('action')) {
			$this->setAttribute('action', '');
		}
		if (!$this->getAttribute('method')) {
			$this->setAttribute('method', 'post');
		}
		$str = '<form ' . $this->getAttributeString() . '>';
		foreach ($this->getElements() as $element) {
			$str .= $element->toString();
		}
		return $str . '</form>';
	}
}