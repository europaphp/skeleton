<?php

/**
 * @author Trey Shugart
 */

/**
 * A class for importing and exporting ini data.
 * 
 * @package Europa
 * @subpackage Ini
 */
class Europa_Ini
{
	/**
	 * Holds the path to the ini file associated with this instance.
	 * 
	 * @var $_file
	 */
	protected $_file = null;
	
	/**
	 * Holds the configuration variables.
	 * 
	 * @var $_vars
	 */
	protected $_vars = array();
	
	/**
	 * Constructs a new ini file object.
	 * 
	 * Takes the file name
	 */
	public function __construct($file)
	{
		$this->_file = $file;
		
		// load the ini file or parse it from a string
		if (is_file($this->_file)) {
			$this->_vars = parse_ini_file($this->_file, true);
		} elseif (function_exists('parse_ini_string')) {
			$this->_vars = parse_ini_string($this->_file, true);
		} else {
			throw new Europa_Ini_Exception(
				'Unable to parse ini file: ' 
				. $file,
				Europa_Ini_Exception::UNABLE_TO_PARSE
			);
		}
		
		// cast as an object
		$this->_vars = (object) $this->_vars;
		
		// cast all sections as objects
		foreach ($this->_vars as &$var) {
			if (is_array($var)) {
				$var = (object) $var;
			}
		}
	}
	
	/**
	 * Retrieves an ini property value.
	 * 
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->_vars->$name;
	}
	
	/**
	 * Sets an ini property value.
	 * 
	 * @return Europa_Ini
	 */
	public function __set($name, $value)
	{
	    $this->_vars->$name = $value;
	}
	
	public function toObject()
	{
		return clone $this->_vars;
	}
	
	/**
	 * Exports the Ini objects to an array.
	 * 
	 * @return array
	 */
	public function toArray()
	{
		$vars = $this->toObject();
		$vars = (array) $vars;
		
		foreach ($vars as &$var) {
			if (is_object($var)) {
				$var = (array) $var;
			}
		}
		
		return $vars;
	}
	
	/**
	 * Saves the modified configuration and overwrites the existing config file
	 * if it exists.
	 * 
	 * @return bool
	 */
	public function save($file = null)
	{
		$iniArray = array();
	    
	    foreach ($this->_vars as $varName => $varVal) {
	    	// the value is an array it is assumed to be a section
	        if (is_object($varVal)) {
	        	// set the section name
	            $iniArray[] = '[' . $varName . ']';
	            
	            // compile the section variables
	            foreach ($varVal as $sectionVarName => $sectionVarValue) {
	            	$iniArray[] = $sectionVarName 
	            	            . ' = ' 
	            	            . self::_quoteIniValue($sectionVarValue);
	            }
	            
	            // so the file is formatted readably
	            $iniArray[] = PHP_EOL;
			// otherwise assume it is not a section
	        } else {
	        	$iniArray[] = $varName . ' = ' . self::_quoteIniValue($varVal);
	        }
	    }
	    
	    // override the existing file name if explicitly set
	    if ($file) {
	    	$this->_file = $file;
	    }
	    
	    // return whether or not the file was able to be written or not
	    return file_put_contents($this->_file, implode(PHP_EOL, $iniArray));
	}
	
	/**
	 * Quotes the ini value. If it is an integer or is defined as a constant
	 * then it is not quoted. Otherwise, it is quoted.
	 * 
	 * @param string $value The value to quote.
	 * 
	 * @return string
	 */
	final static protected function _quoteIniValue($value)
	{
		if (!is_numeric($value) && !defined($value)) {
			$value = '"' . $value . '"';
		}
		
		return $value;
	} 
}