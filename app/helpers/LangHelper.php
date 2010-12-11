<?php

/**
 * A helper for parsing INI language files in the context of a given view.
 * 
 * @category Helpers
 * @package  LangHelper
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class LangHelper
{
	/**
	 * Contains the ini values parsed out of the ini file.
	 * 
	 * @var array
	 */
	protected $ini = array();
	
    /**
     * The language to use.
     * 
     * @var string
     */
    protected static $lang = 'en_US';
    
    /**
     * The base path to the language files.
     * 
     * @var string
     */
    protected static $path;
	
	/**
	 * Constructs the language helper and parses the required ini file.
	 * 
	 * @param Europa_View $view The view that called the helper.
	 * 
	 * @return LangHelper
	 */
	public function __construct(Europa_View $view, $path = null)
	{
	    // allow a path to be specified when constructing
	    if ($path) {
	        self::path($path);
	    }
	    
	    // set a default path if one doesn't exist
	    if (!self::$path) {
	        self::path(dirname(__FILE__) . '/../lang');
	    }
	    
	    // format the path to the ini file
	    $path = self::$path
	          . DIRECTORY_SEPARATOR
	          . self::$lang
	          . DIRECTORY_SEPARATOR
	          . $view->getScript()
	          . '.ini';
        
        // make sure the language fle exists
		if (!file_exists($path)) {
			throw new Europa_Exception('The specified language file does not exist.');
		}
		
		// set the language variables
		$this->ini = parse_ini_file($path);
	}
	
	/**
	 * Allows a language variable to be called as a method. If the first
	 * argument is an array, then named parameters are replaced. If not, then
	 * vsprintf() is used to format the value.
	 * 
	 * Named parameters are prefixed using a colon (:) in the ini value.
	 * 
	 * @param string $name The language variable to retrieve.
	 * @param array  $args The arguments passed to the language variable.
	 * 
	 * @return string
	 */
	public function __call($name, $args)
	{
		$lang = $this->__get($name);
		if (is_array($args[0])) {
			foreach ($args[0] as $name => $value) {
				$lang = str_replace(':' . $name, $value, $lang);
			}
		} else {
			$lang = vsprintf($lang, $args);
		}
		return $lang;
	}
	
	/**
	 * Returns the specified language variable without any formatting.
	 * 
	 * @return string
	 */
	public function __get($name)
	{
		if (isset($this->ini[$name])) {
			return $this->ini[$name];
		}
		return null;
	}
	
	/**
	 * Sets the language to use.
	 * 
	 * @return void
	 */
	static public function set($language)
	{
	    self::$lang = $language;
	}
	
	/**
	 * Sets the base path to the language files.
	 * 
	 * @param string $path The path to the language files.
	 * 
	 * @return mixed
	 */
	static public function path($path = null)
	{
	    $realpath = realpath($path);
	    if (!$realpath) {
	        throw new Europa_Exception('The path to the language files is not valid.');
	    }
	    self::$path = $realpath;
	}
}