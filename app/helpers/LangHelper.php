<?php

/**
 * A helper for parsing INI language files in the context of a given view.
 * 
 * @category Helpers
 * @package  LangHelper
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
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
     * @param Europa\View $view The view that called the helper.
     * 
     * @return LangHelper
     */
    public function __construct(\Europa\View $view, $fileOverride = null, $langOverride = null, $pathOverride = null)
    {
        // set a default path if one doesn't exist
        if (!self::$path) {
            self::path(dirname(__FILE__) . '/../lang');
        }
        
        // allow view script language override
        $file = $fileOverride ? $fileOverride : $view->getScript();
        $lang = $langOverride ? $langOverride : self::$lang;
        $path = $pathOverride ? $pathOverride : self::$path;
        
        // format the path to the ini file
        $path = $path
              . DIRECTORY_SEPARATOR
              . $lang
              . DIRECTORY_SEPARATOR
              . $file
              . '.ini';
        
        // make sure the language fle exists
        if (!file_exists($path)) {
            $e = new \Europa\Exception('The language file "' . $path . '" does not exist.');
            $e->trigger();
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
     * Returns the language variables as an array.
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->_ini;
    }
    
    /**
     * Returns the language variables as a JSON string.
     * 
     * @return array
     */
    public function toJson()
    {
        return json_encode($this->toArray());
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
            $e = new \Europa\Exception('The language file base path "' . $path . '" does not exist.');
            $e->trigger();
        }
        self::$path = $realpath;
    }
}