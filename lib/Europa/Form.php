<?php

/**
 * The main form class which is also an element list.
 * 
 * @package  Europa_Form
 * @license  (c) 2010 Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license	
 */
abstract class Europa_Form extends Europa_Form_ElementList
{
	/**
	 * Renders the form and all of its elements and element lists.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		// build default form structure
		return '<form' 
		     . $this->getAttributeString()
		     . '>'
		     . parent::__toString()
		     . '</form>';
	}
	
	/**
	 * Returns all properties which aren't prefixed with an underscore.
	 * 
	 * @return array
	 */
	public function getAttributes()
	{
		$attributes = array();
		foreach ($this as $k => $v) {
			if (is_numeric($k) || strpos($k, '_') === 0) {
				continue;
			}
			$attributes[$k] = $v;
		}
		return $attributes;
	}
	
	/**
	 * Formats the properties of the element as an xml attribute string.
	 * 
	 * @return string
	 */
	public function getAttributeString()
	{
		$attrs = array();
		foreach ($this->getAttributes() as $k => $v) {
			$attrs[] = $k . '="' . $v . '"';
		}
		return implode(' ', $attrs);
	}
}