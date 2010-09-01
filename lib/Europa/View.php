<?php

/**
 * A base class for views in Europa.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
abstract class Europa_View implements ArrayAccess, Iterator
{
	/**
	 * The parameters and helpers bound to the view.
	 * 
	 * @var array
	 */
	protected $_params = array();
	
	/**
	 * Renders the view in whatever way necessary.
	 * 
	 * @return string
	 */
	abstract public function __toString();
	
	/**
	 * Similar to calling a helper via Europa_View->__call(), but treats the
	 * helper as a singleton and once instantiated, that instance is always
	 * returned for the duration of the Europa_View object's lifespan unless
	 * unset.
	 * 
	 * @param string $name The name of the property to get or helper to load.
	 * @return mixed
	 */
	public function __get($name)
	{
		if (isset($this->_params[$name])) {
			return $this->_params[$name];
		}
		return null;
	}
	
	/**
	 * Sets a parameter.
	 * 
	 * @param string $name  The parameter to set.
	 * @param mixed $value The value to set.
	 * @return bool
	 */
	public function __set($name, $value)
	{
		$this->_params[$name] = $value;
	}
	
	/**
	 * Returns whether a parameter is set or not.
	 * 
	 * @param string $name The parameter to check.
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->_params[$name]);
	}
	
	/**
	 * Unsets a parameter
	 * 
	 * @param string $name The parameter to unset.
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->_params[$name]);
	}
	
	/**
	 * Applies a group of parameters to the view.
	 * 
	 * @param mixed $params The params to set. Can be any iterable value.
	 * @return Europa_View
	 */
	public function setParams($params)
	{
		if (is_array($params) || is_object($params)) {
			foreach ($params as $name => $value) {
				$this->_params[$name] = $value;
			}
		}
		return $this;
	}
	
	/**
	 * Returns the parameters bound to the view.
	 * 
	 * In most cases, this is will only be used when determining which
	 * properties are public internally or when serializing view objects
	 * externally.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		return $this->_params;
	}
	
	public function offsetSet($index, $value)
	{
		$this->$index = $value;
		return $this;
	}
	
	public function offsetGet($index)
	{
		return $this->$index;
	}
	
	public function offsetExists($index)
	{
		return isset($this->$index);
	}
	
	public function offsetUnset($index)
	{
		unset($this->$index);
		return $this;
	}
	
	public function current()
	{
		return current($this->_params);
	}
	
	public function key()
	{
		return key($this->_params);
	}
	
	public function next()
	{
		return next($this->_params);
	}
	
	public function rewind()
	{
		return reset($this->_params);
	}
	
	public function valid()
	{
		return isset($this->{$this->key()});
	}
}