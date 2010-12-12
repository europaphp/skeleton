<?php

/**
 * A helper that autoloads js files.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class JsHelper
{
    /**
     * The css file associated to this instance.
     * 
     * @var string
     */
    protected $file;
    
    /**
     * The path to all css files.
     * 
     * @var string
     */
    protected static $path = 'js';
    
	/**
	 * Constructs the helper.
	 * 
	 * @return CssHelper
	 */
	public function __construct(Europa_View $view, $file = null)
	{
	    if (!$file) {
	        $file = $view->getScript();
	    }
	    $this->file = $file;
	}
	
	/**
	 * Returns the link to the stylesheet.
	 * 
	 * @return string
	 */
	public function __toString()
	{
	    $file = '/';
	    if ($root = Europa_Request_Http::root()) {
	        $file .= $root . '/';
	    }
	    $file .= self::$path . '/' . $this->file . '.js';
	    $path  = $_SERVER['DOCUMENT_ROOT'] . $file;
	    if (file_exists($path)) {
    	    return '<script type="text/javascript" src="' . $file . '"></script>';
    	}
    	return '';
	}
	
	/**
	 * Sets the global css path.
	 * 
	 * @param string $path The path to the css files.
	 * 
	 * @return void
	 */
	public static function path($path = null)
	{
	    self::$path = trim($path, '/');
	}
}