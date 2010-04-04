<?php

/**
 * @author Trey Shugart
 */

/**
 * Class for rendering a basic PHP view script.
 * 
 * If parsing content from a file to render, this class can be overridden
 * to provide base functionality for view manipulation while the __toString
 * method is overridden to provide custom parsing.
 * 
 * @package Europa
 * @subpackage View
 */
class Europa_View_Php extends Europa_View_Abstract
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
	public function __construct($script = null, $params = array())
	{
		// set a script if defined
		if ($script) {
			$this->setScript($script);
		}
		
		// and set arguments
		if (is_array($params)) {
			$this->_params = $params;
		}
	}
	
	/**
	 * Parses the view file and returns the result.
	 * 
	 * @return string
	 */
	public function __toString()
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