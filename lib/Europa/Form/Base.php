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
abstract class Europa_Form_Base
{
	/**
	 * An array of attributes set on the element.
	 * 
	 * @var array
	 */
	protected $_attributes = array();
	
	/**
	 * A way to turn the renderable element into a string.
	 * 
	 * @return string
	 */
	abstract public function toString();
	
	/**
	 * Sets an attribute.
	 * 
	 * @return Europa_Form_Element
	 */
	public function setAttribute($name, $value)
	{
		$this->_attributes[$name] = $value;
		return $this;
	}
	
	/**
	 * Returns the specified attribute or null if not found.
	 * 
	 * @return Europa_Form_Element
	 */
	public function getAttribute($name)
	{
		if (isset($this->_attributes[$name])) {
			return $this->_attributes[$name];
		}
		return null;
	}
	
	/**
	 * Sets an array of attributes all at once.
	 * 
	 * @return Europa_Form_Element
	 */
	public function setAttributes(array $attributes = array())
	{
		foreach ($attributes as $name => $value) {
			$this->_attributes[$name] = $value;
		}
		return null;
	}
	
	/**
	 * Returns the attributes.
	 * 
	 * @return Europa_Form_Element
	 */
	public function getAttributes()
	{
		return $this->_attributes;
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