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
	 * 
	 */
	protected $_attributes = array();
	
	/**
	 * 
	 */
	public function __set($name, $value)
	{
		$this->_attributes[$name] = $value;
		
		return $this;
	}
	
	/**
	 * 
	 */
	public function __get($name)
	{
		return isset($this->_attributes[$name])
		     ? $this->_attributes[$name]
		     : null;
	}
	
	/**
	 * 
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
	 * 
	 */
	public function getAttributes()
	{
		return $this->_attributes;
	}
}