<?php

/**
 * Represents a group of form elements.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Form_ElementList extends Europa_Form_Base implements Iterator
{
	/**
	 * Contains the elements and element lists.
	 * 
	 * @var array
	 */
	protected $_elements = array();
	
	/**
	 * A default way for rendering the form element list.
	 * 
	 * @return string
	 */
	public function toString()
	{
		$str = '<dl' . $this->getAttributeString() . '>';
		foreach ($this as $element) {
			$id   = $element->getAttribute('id');
			$str .= '<dt><label';
			if ($id) {
				$str .= 'for="' . $id . '"';
			}
			$str .= '>' . $element->getAttribute('title') . '</label></dt>'
			     .  '<dd>' . $element->toString() . '</dd>';
		}
		return $str . '</dl>';
	}
	
	/**
	 * Adds a valid renderable element onto the element list.
	 * 
	 * @param Europa_Form_Renderable $element The element to add.
	 * @return Europa_Form_ElementList
	 */
	public function addElement(Europa_Form_Base $element)
	{
		$this->_elements[] = $element;
		return $this;
	}
	
	/**
	 * Returns the form elements applied to this list.
	 * 
	 * @return array
	 */
	public function getElements()
	{
		return $this->_elements;
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
	
	/**
	 * Validates each field in the list.
	 * 
	 * @return Europa_Form_ElementList
	 */
	public function validate()
	{
		foreach ($this as $element) {
			$element->validate();
		}
		return $this;
	}
	
	/**
	 * Counts the array elements.
	 * 
	 * @return int
	 */
	public function count()
	{
		return count($this->_elements);
	}
	
	/**
	 * Returns the current item.
	 * 
	 * @return mixed
	 */
	public function current()
	{
		return current($this->_elements);
	}
	
	/**
	 * Returns the key of the current element.
	 * 
	 * @return mixed
	 */
	public function key()
	{
		return key($this->_elements);
	}
	
	/**
	 * sets the next element in the array.
	 * 
	 * @return Europa_elements
	 */
	public function next()
	{
		next($this->_elements);
		return $this;
	}
	
	/**
	 * Rewinds the array.
	 * 
	 * @return Europa_elements
	 */
	public function rewind()
	{
		reset($this->_elements);
		return $this;
	}

	/**
	 * Returns whether or not the array can still be iterated over.
	 * 
	 * @return bool
	 */
	public function valid()
	{
		return (bool) $this->current();
	}
}