<?php

namespace Europa\Fs;

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
     * Loads a class if it can find it and returns whether or not it was loaded.
     * 
     * @param string $class The class to search for.
     * 
     * @return Loader
     */
    public function __invoke($class)
    {
        if (class_exists($class, false)) {
            return true;
        }
        
        if ($this->locator && $file = call_user_func($this->locator, $class . '.php')) {
            include $file;
            return true;
        }

        if (is_file($file = __DIR__ . '/../../' . $class . '.php')) {
            include $file;
            return true;
        }
        
        return false;
    }
    
    /**
     * Sets the locator.
     * 
     * @param callable $locator The locator to use for locating class files.
     * 
     * @return Loader
     */
    public function setLocator(callable $locator)
    {
        $this->locator = $locator;
        return $this;
    }
    
    /**
     * Returns the locator.
     * 
     * @return callable
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * Returns whether or not the loader has a locator.
     * 
     * @return bool
     */
    public function hasLocator()
    {
        return isset($this->locator);
    }

    /**
     * Removes the locator from the loader.
     * 
     * @return Loader
     */
    public function removeLocator()
    {
        $this->locator = null;
        return $this;
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
        spl_autoload_register(array($this, '__invoke'), true, $prepend);
        return $this;
    }
}