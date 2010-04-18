<?php

/**
 * The main form class which is also an element list.
 * 
 * @category Forms
 * @package  Europa_Form
 * @license  Copyright 2010  Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license	
 */
abstract class Europa_Form extends Europa_Form_ElementList
{
	/**
	 * The attributes applied to the form.
	 * 
	 * @var array
	 */
	protected $_attributes = array();
	
	/**
	 * Sets an attribute.
	 * 
	 * @param string $name  The attribute name.
	 * @param mixed  $value The attribute value.
	 * 
	 * @return Europa_Form
	 */
	public function __set($name, $value)
	{
		$this->_attributes[$name] = $value;
		
		return $this;
	}
	
	/**
	 * Retrieves an attribute value.
	 * 
	 * @param string $name The attribute name.
	 * 
	 * @return mixed
	 */
	public function __get($name)
	{
		return isset($this->_attributes[$name])
		     ? $this->_attributes[$name]
		     : null;
	}
	
	/**
	 * Renders the form and all of its elements and element lists.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		// build attribute list
		$attributes = '';
		foreach ($this->getAttributes() as $attr => $value) {
			$attributes .= ' ' . $attr . '="' . $value . '"';
		}
		
		// build default form structure
		return '<form' . $attributes . '>'
		     . parent::__toString()
		     . '</form>';
	}
	
	/**
	 * Returns an array of the attributes applied to the form.
	 * 
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->_attributes;
	}
}