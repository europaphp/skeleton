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
abstract class Europa_Form_Element extends Europa_Form_Base
{
	/**
	 * The name of the element.
	 * 
	 * @var string
	 */
	protected $_name;
	
	/**
	 * The value of the element.
	 * 
	 * @var mixed
	 */
	protected $_value = null;
	
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
	public function __construct($name, $value = null, array $attributes = array())
	{
		$this->setName($name);
		$this->setValue($value);
		$this->setAttributes($attributes);
	}
	
	/**
	 * Automatically retrieves the value for the input field base on its name
	 * from the passed in values.
	 * 
	 * @param string $name The name of the field to retrieve the value for.
	 * @param mixed $values The values to find the value in.
	 * @return Europa_Form_Element
	 */
	public function fill($values)
	{
		// if no values are set, then do nothing
		if (!$values) {
			return $this;
		}
		
		$subs = '';
		$name = $this->getName();
		
		// parse out the names and format it
		if (strpos($name, '[') !== false) {
			$subs = explode('[', $name);
			array_shift($subs);
			$formatted = array();
			foreach ($subs as $sub) {
				$sub = str_replace(']', '', $sub);
				if ($sub === '') {
					continue;
				}
				if (is_numeric($sub)) {
					$formatted[] = $sub;
				} else {
					$formatted[] = "'{$sub}'";
				}
			}
			$subs = '[' . implode('][', $formatted) . ']';
		} else {
			$subs = "['{$name}']";
		}
		
		// if it's just a straight value, set it
		if (!is_array($values) && !is_object($values)) {
			$this->setValue($values);
		}
		
		// build the parameter to evaluate then eval
		$value = '$values' . $subs;
		$value = eval("return isset({$value}) ? {$value} : false;");
		
		if ($value !== false) {
			$this->setValue($value);
		}
		
		return $this;
	}
	
	/**
	 * Sets the name of the element.
	 * 
	 * @param string $name The name of the element.
	 * @return Europa_Form_Element
	 */
	public function setName($name)
	{
		return $this->setAttribute('name', $name);
	}

	/**
	 * Gets the name of the element.
	 * 
	 * @return Europa_Form_Element
	 */
	public function getName()
	{
		return $this->getAttribute('name');
	}
	
	/**
	 * Sets the value of the element.
	 * 
	 * @param string $value The value of the element.
	 * @return Europa_Form_Element
	 */
	public function setValue($value)
	{
		return $this->setAttribute('value', $value);
	}

	/**
	 * Gets the value of the element.
	 * 
	 * @return Europa_Form_Element
	 */
	public function getValue()
	{
		return $this->getAttribute('value');
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
			if (!$validator->isValid($this->getValue())) {
				$this->_errors[] = $validator;
			}
		}
		return $this;
	}
}