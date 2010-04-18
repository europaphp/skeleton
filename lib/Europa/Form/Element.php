<?php

abstract class Europa_Form_Element extends Europa_Array implements Europa_Form_Renderable
{
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
	 * The default name setter.
	 * 
	 * @param string $name
	 * 
	 * @return Europa_Form_Element
	 */
	protected function _setName($name)
	{
		// format each part of the name
		$names = explode('[', $name);
		foreach ($names as &$name) {
			$name = Europa_String::create($name)->camelCase();
		}
		
		// build the name
		$name = array_shift($names);
		if ($names) {
			$name .= '[' . implode(']', $names) . ']';
		}
		
		// the id should be the same as the name
		$this->_array['id']   = (string) Europa_String::create($name->__toString())->camelCase();
		$this->_array['name'] = (string) $name;
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
	final public function fill($values)
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
}