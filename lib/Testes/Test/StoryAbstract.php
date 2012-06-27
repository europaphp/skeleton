<?php

namespace Testes\Test;
use LogicException;

abstract class StoryAbstract extends UnitAbstract
{
	public function given($given)
	{
		return $this->call('given', $given, func_get_args());
	}
	
	public function when($when)
	{
		return $this->call('when', $when, func_get_args());
	}
	
	public function then($then)
	{
		return $this->call('then', $then, func_get_args());
	}
	
	private function call($type, $method, array $args = array())
	{
		// the first item will be the type of call so remove it
		array_shift($args);
		
		// format the method
		$method = $this->format($method);
		$method = $type . $method;
		
		// check if the method exists
		if (!method_exists($this, $method)) {
		    throw new LogicException('You did not define a test method for "' . $method . '".');
		}
		
		// call the method
		call_user_func_array(array($this, $method), $args);
		
		return $this;
	}
	
	private function format($str)
	{
		$str = preg_replace('/[^a-zA-Z]/', ' ', $str);
		$str = ucwords($str);
		$str = str_replace(' ', '', $str);
		return $str;
	}
}