<?php

/**
 * @author  Trey Shugart
 * @license http://europaphp.org/license
 */

/**
 * Handles the rendering and automatic value detenction of form elements.
 */
class Europa_Form_Element extends Europa_View
{
	public
		/**
		 * The id of the element. If this is left blank, it will be made similar
		 * or the same as the name.
		 * 
		 * @var $id
		 */
		$id = null,
		
		/**
		 * The name of the form element.
		 * 
		 * @var $name
		 */
		$name = null,
		
		/**
		 * The value of the element. Can be an array if it is a multi-select.
		 * 
		 * @var $value
		 */
		$value = null,
		
		/**
		 * The default value for the element.
		 * 
		 * @var $defaultValue
		 */
		$defaultValue = null;
	
	/**
	 * Matches the name and id if they are set and then renders the element.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		// match the id and the name if both are set then it doesn't do anything
		$this->_matchIdAndName();
		
		return parent::__toString();
	}
	
	/**
	 * Automatically retrieves the value for the input field base on its name
	 * from the passed in values.
	 * 
	 * @param string $name   The name of the field to retrieve the value for.
	 * @param mixed  $values The values to find the value in.
	 * 
	 * @todo Optimize performance.
	 * @todo Do more security tests on call to eval.
	 */
	final public function fill($values)
	{
		$subs = '';
		$name = $this->name;
		
		// parse out the names and format it
		if (strpos($name, '[') !== false) {
			$subs = explode('[', $name);
			$name = array_shift($subs);
			$subs = '[' . implode('[', $subs);
			$subs = str_replace('[', "['", $subs);
			$subs = str_replace(']', "']", $subs);
		}
		
		// if it's just a straight value, set it
		if (!is_array($values) && !is_object($values)) {
			$this->value = $value;
		}
		
		// build the parameter to evaluate
		$evalParam = '$values[\'' . $name . '\']' . $subs;
		
		// evaluate the value
		$value = eval("return isset({$evalParam}) ? {$evalParam} : false;");
		
		if ($value !== false) {
			$this->value = $value;
		}
		
		return $this;
	}
	
	/**
	 * Overrides Europa_View::_getViewPath() to return the path to the default
	 * form element path.
	 * 
	 * @return string
	 */
	protected function _getViewPath()
	{
		return realpath('./app/forms/elements');
	}
	
	/**
	 * Matches the id and name so that they follow a specific naming convention.
	 * 
	 * By default, ids and names are camelcased. Array identifiers are allowed
	 * in names, but are stripped from ids.
	 * 
	 * This can be overridden to provide custom behavior and naming convention.
	 * 
	 * @return Europa_Form_Element
	 */
	protected function _matchIdAndName()
	{
		$base = null;
		
		if ($this->name) {
			$base = $this->name;
		} elseif ($this->id) {
			$base = $this->id;
		}
		
		// match the name and id
		if ($base) {
			$new = Europa_String::create($base);
			$new = (string) $new->camelCase();
			
			$this->id   = $new;
			$this->name = $base;
		}
		
		return $this;
	}
}