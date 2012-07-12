<?php

namespace Europa\Fs;
use Europa\Fs\Locator;

/**
 * Default autoload registration for library files.
 * 
 * @return void
 */
spl_autoload_register(function($class) {
    $file = str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $class);
    $file = $file . '.php';
    $file = __DIR__ . '/../../' . $file;
    
    if ($real = realpath($file)) {
        include $real;
    }
});

/**
 * Handles class loading.
 * 
 * @category ClassLoading
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Loader
{
    /**
     * The locator to use for locating class files.
     * 
     * @var LocatorInterface
     */
    private $locator;
    
    /**
     * Sets up the loader.
     * 
     * @return Loader
     */
    public function __construct()
    {
        $this->locator = new Locator;
    }
    
    /**
     * Sets the locator.
     * 
     * @param LocatorInterface $locator The locator to use for locating class files.
     * 
     * @return Loader
     */
    public function setLocator(LocatorInterface $locator)
    {
        $this->locator = $locator;
        return $this;
    }
    
    /**
     * Returns the locator.
     * 
     * @return LocatorInterface
     */
    public function getLocator()
    {
        return $this->locator;
    }
    
    /**
     * Searches for a class, loads it if it is found and returns whether or not it was loaded.
     * 
     * The Europa install directory is searched first. If it is not found and a locator is defined, the locator is used
     * to locate the class.
     * 
     * @param string $class The class to search for.
     * 
     * @return Loader
     */
    public function load($class)
    {
        if (class_exists($class, false)) {
            return true;
        }
        
        if ($class = $this->resolve($class)) {
            include $class;
            return true;
        }
        
        return false;
    }
    
    /**
     * Resolves the path to the specified class.
     * 
     * @param string $class The class to find.
     * 
     * @return mixed
     */
    public function resolve($class)
    {
        if ($this->locator && $fullpath = $this->locator->locate($this->normalize($class))) {
            return $fullpath;
        }
        return false;
    }
    
    /**
     * Registers the auto-load handler and automatically registers the Europa install path to the load paths.
     * 
     * @param bool $prepend Whether or not to prepend the autoloader onto the stack.
     * 
     * @return Loader
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'load'), true, $prepend);
        return $this;
    }
    
    /**
     * Normalizes the file.
     * 
     * @param string $file The file to normalize.
     * 
     * @return string
     */
    private function normalize($class)
    {
        $file = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $class);
        $file = trim($file, DIRECTORY_SEPARATOR);
        return $file;
    }
}