<?php

/**
 * Abstract element class which represents any element on a form.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
abstract class Europa_Form_Element extends Europa_Overloader implements Europa_Form_Renderable
{
	/**
	 * Contains instances of Europa_Validator objects representing which
	 * validators failed.
	 * 
	 * @var array
	 */
	protected $_errors = array();
	
	/**
	 * Contains the validators applied to the element.
	 * 
	 * @var array
	 */
	protected $_validators = array();
	
	/**
	 * Initializes the form element and sets its name.
	 * 
	 * @param string $name The name of the element.
	 * 
	 * @return Europa_Form_Element
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}
	
	/**
	 * Automatically retrieves the value for the input field base on its name
	 * from the passed in values.
	 * 
	 * @todo Optimize performance.
	 * @todo Do more security tests on call to eval.
	 * 
	 * @param string $name The name of the field to retrieve the value for.
	 * @param mixed $values The values to find the value in.
	 * 
	 * @return Europa_Form_Element
	 */
	public function fill($values)
	{
		// if no values are set, then do nothing
		if (!$values) {
			return $this;
		}
		
		$subs = '';
		$name = $this->name;
		
		// parse out the names and format it
		if (strpos($name, '[') !== false) {
			$subs = explode('[', $name);
			$subs = '[' . implode('[', $subs);
			$subs = str_replace('[', "['", $subs);
			$subs = str_replace(']', "']", $subs);
		} else {
			$subs = "['{$name}']";
		}
		
		// if it's just a straight value, set it
		if (!is_array($values) && !is_object($values)) {
			$this->value = $values;
		}
		
		// build the parameter to evaluate
		$evalParam = '$values' . $subs;
		
		// evaluate the value
		$value = eval("return isset({$evalParam}) ? {$evalParam} : false;");
		
		if ($value !== false) {
			$this->value = $value;
		}
		
		return $this;
	}
	
	/**
	 * Returns whether or not the element has any errors.
	 * 
	 * @return bool
	 */
	public function hasErrors()
	{
		return count($this->getErrors()) > 0;
	}
	
	/**
	 * Returns all validator instances which failed.
	 * 
	 * @return array
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
	
	/**
	 * Adds a validator to the element.
	 * 
	 * @param Europa_Validator $validator The validator to add.
	 * 
	 * @return Europa_Form_Element
	 */
	public function addValidator(Europa_Validator $validator)
	{
		$this->_validators[] = $validator;
		
		return $this;
	}
	
	/**
	 * Runs all validators against the current value of the element.
	 * 
	 * @return Europa_Form_Element
	 */
	public function validate()
	{
		foreach ($this->_validators as $validator) {
			if (!$validator->isValid()) {
				$this->_errors[] = $validator;
			}
		}
		
		return $this;
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
			if (strpos($k, '_') === 0) {
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