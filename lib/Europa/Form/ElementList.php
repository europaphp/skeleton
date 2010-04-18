<?php

/**
 * Represents a group of form elements.
 * 
 * @category Forms
 * @package  Europa_Form
 * @license  Copyright 2010  Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license
 */
class Europa_Form_ElementList extends Europa_Array implements Europa_Form_Renderable
{
	/**
	 * Converts the list of elements to a string.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$str = '';
		foreach ($this as $element) {
			$str .= $element->__toString();
		}
		return $str;
	}
	
	/**
	 * Adds a valid renderable element onto the element list.
	 * 
	 * @param Europa_Form_Renderable $element The element to add.
	 * 
	 * @return Europa_Form_ElementList
	 */
	public function addElement(Europa_Form_Renderable $element)
	{
		parent::offsetSet(null, $element);
		
		return $this;
	}

	/**
	 * Overrides the parent setter to make sure the passed in value is a valid
	 * element.
	 * 
	 * @param mixed $offset The offset to set.
	 * @param mixed $value  The value to set.
	 * 
	 * @return Europa_Form_ElementList
	 */
	public function offsetSet($offset, $value)
	{
		return $this->addElement($value);
	}
	
	/**
	 * Takes an array of values and searches for a matching value for each
	 * element in the list. Recursively handles nested element lists.
	 * 
	 * @param array $values An array of values to search in.
	 * 
	 * @return Europa_Form_ElementList
	 */
	public function fill($values)
	{
		foreach ($this as $element) {
			$element->fill($values);
		}

		return $this;
	}
}