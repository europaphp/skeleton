<?php

namespace Europa\Fs;

/**
 * Handles class loading.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Loader
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
     * @return \Europa\Fs\Loader
     */
    public function load($class)
    {
        if (class_exists($class, false)) {
            return true;
        }
        
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        
        // attempt loading of classes at same level as framework
        if ($fullpath = realpath(dirname(__FILE__) . '/../../' . $file . '.php')) {
            require_once $fullpath;
            return $this;
        }
        
        // attempt loading from other sources
        if ($this->locator) {
            if ($fullpath = $this->locator->locate($file)) {
                require_once $fullpath;
                return $this;
            }
        }
        
        require_once realpath(dirname(__FILE__) . '/../Exception.php');
        require_once realpath(dirname(__FILE__) . '/Exception.php');
        throw new Exception("Unable to load class {$class}.");
    }
    
    /**
     * Sets the locator.
     * 
     * @param \Europa\Fs\LocatorInterface $locator The locator to use for locating class files.
     * 
     * @return \Europa\Fs\Loader
     */
    public function setLocator(LocatorInterface $locator)
    {
        $this->locator = $locator;
        return $this;
    }
    
    /**
     * Registers the auto-load handler and automatically registers the Europa install path to the load paths.
     * 
     * @return \Europa\Fs\Loader
     */
    public function register()
    {
        spl_autoload_register(array($this, 'load'));
        return $this;
    }
}