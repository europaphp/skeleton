<?php

namespace Europa;
use Europa\Fs\Locator\LocatorInterface;

/**
 * Handles class loading.
 * 
 * @category ClassLoading
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class ClassLoader
{
    /**
     * The locator to use for locating class files.
     * 
     * @var \Europa\Fs\Locator\LocatorInterface
     */
    private $locator;
    
    /**
     * Searches for a class, loads it if it is found and returns whether or not it was loaded.
     * 
     * The Europa install directory is searched first. If it is not found and a locator is defined, the locator is used
     * to locate the class.
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
     * Sets the locator. A locator is not required for class loading to work.
     * 
     * @param \Europa\Fs\Locator\LocatorInterface $locator The locator to use for locating class files.
     * 
     * @return \Europa\ClassLoader
     */
    public function setLocator(LocatorInterface $locator)
    {
        $this->locator = $locator;
        return $this;
    }
    
    /**
     * Registers the auto-load handler and automatically registers the Europa install path to the load paths.
     * 
     * @param bool $prepend Whether or not to prepend the autoloader onto the stack.
     * 
     * @return \Europa\ClassLoader
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'load'), true, $prepend);
        return $this;
    }
}
