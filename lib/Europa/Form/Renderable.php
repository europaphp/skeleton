<?php

/**
 * The base interface for all renderable form elements.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
interface Europa_Form_Renderable
{
	/**
	 * Sets an attribute value.
	 * 
	 * @param string $name The name of the attribute.
	 * @param string $value The value of the attribute.
	 * @return mixed
	 */
	public function __set($name, $value);
	
	/**
	 * Returns an attribute value.
	 * 
	 * @param string $name The name of the attribute.
	 * @return mixed
	 */
	public function __get($name);
	
	/**
	 * Returns whether or not an attribute exists.
	 * 
	 * @param string $name The attribute name.
	 * @return bool
	 */
	public function __isset($name);

	/**
	 * Removes an attribute.
	 * 
	 * @param string $name The attribute name.
	 * @return bool
	 */
	public function __unset($name);
	
	/**
	 * Sets an attribute value.
	 * 
	 * @param string $name The name of the attribute.
	 * @param string $value The value of the attribute.
	 * @return mixed
	 */
	public function setAttribute($name, $value);
	
	/**
	 * Returns an attribute value.
	 * 
	 * @param string $name The name of the attribute.
	 * @return mixed
	 */
	public function getAttribute($name);
	
	/**
	 * Returns whether or not an attribute exists.
	 * 
	 * @param string $name The attribute name.
	 * @return bool
	 */
	public function hasAttribute($name);
	
	/**
	 * Removes an attribute.
	 * 
	 * @param string $name The attribute name.
	 * @return bool
	 */
	public function removeAttribute($name);
	
	/**
	 * Sets an array of attributes all at once.
	 * 
	 * @return Europa_Form_Element
	 */
	public function setAttributes(array $attributes = array());
	
	/**
	 * Returns the attributes.
	 * 
	 * @return Europa_Form_Element
	 */
	public function getAttributes();

	/**
	 * Formats the properties of the element as an xml attribute string.
	 * 
	 * @return string
	 */
	public function getAttributeString();
}