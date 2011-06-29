<?php

namespace Europa;
use Europa\Fs\Locator;

/**
 * Handles class loading.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class ClassLoader
{
    /**
     * The locator to use for locating class files.
     * 
     * @var \Europa\Fs\Locator
     */
    private $locator;
    
    /**
     * Searches for a class and loads it if it is found. If the class is found, true is returned. Otherwise false is
     * returned.
     * 
     * @param string $class The class to search for.
     * 
     * @return \Europa\ClassLoader
     */
    public function load($class)
    {
        if (class_exists($class, false)) {
            return true;
        }
        
        // normalize
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $file = trim($file, DIRECTORY_SEPARATOR);
        
        // attempt loading of classes at same level as framework
        if ($fullpath = realpath(dirname(__FILE__) . '/../' . $file . '.php')) {
            include $fullpath;
            return true;
        }
        
        // attempt loading from other sources
        if ($this->locator) {
            if ($fullpath = $this->locator->locate($file)) {
                include $fullpath;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sets the locator.
     * 
     * @param \Europa\Fs\Locator $locator The locator to use for locating class files.
     * 
     * @return \Europa\ClassLoader
     */
    public function setLocator(Locator $locator)
    {
        $this->locator = $locator;
        return $this;
    }
    
    /**
     * Registers the auto-load handler and automatically registers the Europa install path to the load paths.
     * 
     * @return \Europa\ClassLoader
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'load'), true, $prepend);
        return $this;
    }
}