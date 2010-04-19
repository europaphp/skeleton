<?php

/**
 * A class built to automate getters, setters, isset checking and unsetters.
 * 
 * 
 */
abstract class Europa_Overloader
{
	/**
	 * Checks for a getter and returns it's value if it exists. Otherwise, it
	 * attempts to retrieve the property directly.
	 * 
	 * @param string $name The name of the property.
	 * 
	 * @return mixed
	 */
	public function __get($name)
	{
		$method = $this->_formatOverloadedName(__FUNCTION__, $name);
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		if (isset($this->$name)) {
			return $this->$name;
		}
		return null;
	}
	
	/**
	 * Checks for an [prefix]isset[PropertyName] method and runs it if it 
	 * exists. Otherwise, it attempts to check the property directly.
	 * 
	 * @param string $name The name of the property.
	 * 
	 * @return mixed
	 */
	public function __isset($name)
	{
		$method = $this->_formatOverloadedName(__FUNCTION__, $name);
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		return isset($this->$name);
	}
	
	/**
	 * Checks for a getter and returns it's value if it exists. Otherwise, it
	 * attempts to retrieve the property directly.
	 * 
	 * @param string $name The name of the property.
	 * 
	 * @return mixed
	 */
	public function __set($name, $value)
	{
		$method = $this->_formatOverloadedName(__FUNCTION__, $name);
		if (method_exists($this, $method)) {
			$this->$method($value);
			return;
		}
		$this->$name = $value;
	}
	
	/**
	 * Checks for a setter and runs it if it exists. Otherwise, it attempts to
	 * set the property directly.
	 * 
	 * @param string $name  The name of the property.
	 * @param mixed  $value The value to set.
	 * 
	 * @return mixed
	 */
	public function __unset($name)
	{
		$method = $this->_formatOverloadedName(__FUNCTION__, $name);
		if (method_exists($this, $method)) {
			$this->$method();
			return;
		}
		if (isset($this->$name)) {
			unset($this->$name);
		}
	}
	
	/**
	 * Returns a formatted method name to call based on the type of magic
	 * method which was called and the name which was passed to it.
	 * 
	 * @param string $type The type of method called, i.e. __get, __set, etc.
	 * @param string $name The name of the property being accessed.
	 * 
	 * @return string
	 */
	protected function _formatOverloadedName($type, $name)
	{
		return str_replace('__', '', $type) . $name;
	}
}