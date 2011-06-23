<?php

namespace Helper;
use Europa\Exception;
use Europa\Fs\Locator;
use Europa\View\Php;

/**
 * A helper for parsing INI language files in the context of a given view.
 * 
 * @category Helpers
 * @package  LangHelper
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Lang
{
    /**
     * Contains the ini values parsed out of the ini file.
     * 
     * @var array
     */
    private $cache = array();
    
    private $lang;
    
    private $locator;
    
    private $view;
    
    /**
     * Constructs the language helper and parses the required ini file.
     * 
     * @return \LangHelper
     */
    public function __construct(Locator $locator, Php $view, $lang)
    {
        $this->locator = $locator;
        $this->view    = $view;
        $this->lang    = $lang;
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
     * Returns the specified language variable without any formatting. If the
     * variable isn't found, the name is passed through and returned.
     * 
     * @return string
     */
    public function __get($name)
    {
        $this->reParseIfDifferentView();
        $view = $this->view->getScript();
        if (isset($this->cache[$view][$name])) {
            return $this->cache[$view][$name];
        }
        return $name;
    }
    
    /**
     * Returns the language variables as an array.
     * 
     * @return array
     */
    public function toArray()
    {
        $this->reParseIfDifferentView();
        $view = $this->view->getScript();
        if (isset($this->cache[$view][$name])) {
            return $this->cache[$view][$name];
        }
        return array();
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
     * Re-parses the ini file if a different view is detected.
     * 
     * @return \Helper\Lang
     */
    private function reParseIfDifferentView()
    {
        $view = $this->view->getScript();
        if (!isset($this->cache[$view])) {
            if ($path = $this->locator->locate("{$this->lang}/{$view}")) {
                $this->cache[$view] = parse_ini_file($path);
            } else {
                $this->cache[$view] = array();
            }
        }
        return $this;
    }
}