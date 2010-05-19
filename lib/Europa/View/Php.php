<?php

/**
 * Class for rendering a basic PHP view script.
 * 
 * If parsing content from a file to render, this class can be overridden
 * to provide base functionality for view manipulation while the __toString
 * method is overridden to provide custom parsing.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_View_Php extends Europa_View
{
	/**
	 * The script to be rendered.
	 * 
	 * @var string
	 */
	protected $_script = null;
	
	/**
	 * Construct the view and sets defaults.
	 * 
	 * @param string $script The script to render.
	 * @param array $params The arguments to pass to the script.
	 * @return Europa_View
	 */
	public function __construct($script = null, array $params = array())
	{
		// set a script if defined
		if ($script) {
			$this->setScript($script);
		}
		$this->setParams($params);
	}

	/**
	 * If a parameter is already set it is returned. If a parameter is not set
	 * it's similar to calling a helper via Europa_View->__call(), but treats
	 * the helper as a singleton and once instantiated, that instance is always
	 * returned for the duration of the Europa_View object's lifespan unless
	 * unset.
	 * 
	 * @param string $name The name of the property to get or helper to load.
	 * @return mixed
	 */
	public function __get($name)
	{
		if ($value = parent::__get($name)) {
			return $value;
		}
		$helper = $this->__call($name);
		if ($helper) {
			$this->_params[$name] = $helper;
			return $helper;
		}
		return null;
	}

	/**
	 * Attempts to load a helper and executes it. Returns null of not found.
	 * 
	 * @return mixed
	 */
	public function __call($func, $args = array())
	{
		// format the helper class name for the given method
		$class = $this->_getHelperClassName($func);

		// if unable to load, return null
		if (!Europa_Loader::loadClass($class)) {
			return null;
		}

		// instantiate the helper and pass in the current view
		$class = new $class($this);

		// if a helper methods exists, call it with $args and return the value
		if (method_exists($class, $func)) {
			return call_user_func_array(array($class, $func), $args);
		}

		// or just return the helper instance
		return $class;
	}
	
	/**
	 * Parses the view file and returns the result.
	 * 
	 * @return string
	 */
	public function toString()
	{
		// allows us to return the included file as a string
		ob_start();
		
		// include it
		if ($path = $this->_getScriptFullPath()) {
			include $path;
		}
		
		// return the parsed view
		return ob_get_clean() . "\n";
	}
	
	/**
	 * Sets the script to be rendered.
	 * 
	 * @param String $script The path to the script to be rendered relative 
	 * to the view path, excluding the extension.
	 * @return Object Europa_View
	 */
	public function setScript($script)
	{
		$this->_script = $script;
		
		return $this;
	}
	
	/**
	 * Returns the script that is going to be rendered.
	 * 
	 * @return string
	 */
	public function getScript()
	{
		return $this->_script;
	}
	
	/**
	 * Returns the full path to the view including extension.
	 * 
	 * @return string
	 */
	protected function _getScriptFullPath()
	{
		return realpath('./app/views/' . $this->_script . '.php');
	}
}