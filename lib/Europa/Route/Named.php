<?php

class Europa_Route_Named implements Europa_Route
{
	private $_hasWildcard = false;
	
	private $_expression;
	
	private $_defaults;
	
	public function __construct($expression, array $defaults = array())
	{
		$expressionLength   = strlen($expression);
		$this->_defaults    = $defaults;
		$this->_hasWildcard = $expression[$expressionLength - 1] === '*';
		$this->_expression  = $this->_hasWildcard 
		                    ? substr($expression, 0, $expressionLength - 2) 
		                    : $expression;
	}
	
	public function query($subject)
	{
		$expressionParts = explode('/', $this->_expression);
		$subjectParts    = explode('/', $subject);
		
		// if they aren't the same length, then they don't match
		if (!$this->_hasWildcard && count($expressionParts) !== count($subjectParts)) {
			return false;
		}
		
		$params = array();
		
		// map paramters from the subject
		while (list($index, $subjectPart) = each($subjectParts)) {
			// the subject may be longer than the expression if a wildcard is used
			if (!isset($expressionParts[$index])) {
				break;
			}
			
			// grab the part of the expression that corresponds to this subject part
			$expressionPart = $expressionParts[$index];
			
			// if we are on a named parameter, then set it
			if ($expressionPart[0] === ':') {
				$params[substr($expressionPart, 1)] = $subjectPart;
			// otherwise we need to check to make sure the parts are equal
			} elseif ($expressionPart !== $subjectPart) {
				return false;
			}
		}
		
		// set defaults
		while (list($name, $value) = each($this->_defaults)) {
			if (!isset($params[$name])) {
				$params[$name] = $value;
			}
		}
		
		return $params;
	}
	
	public function reverse(array $params = array())
	{
		$reverse = $this->_expression;
		while (list($name, $value) = each($params)) {
			$reverse = str_replace(':' . $name, $value, $reverse);
		}
		return $reverse;
	}
}