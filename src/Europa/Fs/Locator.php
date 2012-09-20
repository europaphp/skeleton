<?php

namespace Europa\Fs;
use InvalidArgumentException;
use LogicException;

/**
 * Handles file locating based on cascading custom paths.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Locator implements LocatorInterface
{
    /**
     * The default file suffix.
     * 
     * @var string
     */
    const DEFAULT_SUFFIX = 'php';

    /**
     * The base path to make all paths relative to. If not supplied, it is not used.
     * 
     * @var string
     */
    private $base;
    
    /**
     * Contains all load paths that Europa\RouteLoader will use when searching for a file.
     * 
     * @var array
     */
    private $paths = array();
    
    /**
     * Maps classes to their absolute paths.
     * 
     * @var array
     */
    private $map = array();
    
    /**
     * Whether or not to throw an exception when adding a path if the path does not exist.
     * 
     * @var bool
     */
    private $throwWhenAdding = true;
    
    /**
     * Whether or not to throw an exception when if the file is not found when locating.
     * 
     * @var bool
     */
    private $throwWhenLocating = false;

    public function setBasePath($base)
    {
        $real = realpath($base);
        
        if (!$real) {
            throw new InvalidArgumentException(sprintf('The base path "%s" does not exist.', $base));
        }
        
        $this->base = $real;
        
        return $this;
    }

    public function getBasePath()
    {
        return $this->base;
    }
    
    /**
     * Sets whether or not to throw an exception when if the file is not found when locating.
     * 
     * @param bool $switch Turns throwing on or off.
     * 
     * @return Locator
     */
    public function throwWhenAdding($switch = true)
    {
        $this->throwWhenAdding = $switch ? true : false;
        return $this;
    }
    
    /**
     * Sets whether or not to throw an exception when if the file is not found when locating.
     * 
     * @param bool $switch Turns throwing on or off.
     * 
     * @return Locator
     */
    public function throwWhenLocating($switch = true)
    {
        $this->throwWhenLocating = $switch ? true : false;
        return $this;
    }
    
    /**
     * Maps the specified item to the specified file.
     * 
     * @param string $map  The class to map.
     * @param string $file The file to map to.
     * 
     * @return Locator
     */
    public function map($map, $file)
    {
        $this->map[$map] = $file;
        return $this;
    }
    
    /**
     * Returns the class mapping.
     * 
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }
    
    /**
     * Maps an array of mappings.
     * 
     * @param array $maps The mapping array.
     * 
     * @return Locator
     */
    public function setMap(array $maps)
    {
        foreach ($maps as $map => $file) {
            $this->map($map, $file);
        }
        return $this;
    }
    
    /**
     * Searches for the specified file and returns the file if found or false if not found. If the class is found, it
     * is cached in the class map.
     * 
     * @param string $class The class to search for.
     * 
     * @return bool | string
     */
    public function locate($file)
    {
        // normalize
        $file = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $file);
        $file = trim($file, DIRECTORY_SEPARATOR);
        
        // if it exists in the map just return it
        if (isset($this->map[$file])) {
            return $this->map[$file];
        }
        
        // search in paths
        foreach ($this->paths as $path => $suffixes) {
            // if the file still isn't found, apply suffixes
            foreach ($suffixes as $suffix) {
                $fullpath = $path . DIRECTORY_SEPARATOR . $file . '.' . $suffix;
                $fullpath = realpath($fullpath);
                
                if ($fullpath) {
                    $this->map($file, $fullpath);
                    return $fullpath;
                }
            }
        }
        
        if ($this->throwWhenLocating) {
            throw new LogicException(sprintf('Could not locate the file "%s".', $file));
        }
        
        return false;
    }
    
    /**
     * Adds a path to the load paths. Uses realpath to determine path validity. If the path is unable to be resolve, an
     * exception is thrown if throwing is turned on.
     * 
     * @param string $path   The path to add to the list of load paths.
     * @param mixed  $suffix The suffix, or suffixes to use for this path.
     * 
     * @return Locator
     */
    public function addPath($path, $suffix = self::DEFAULT_SUFFIX)
    {
        // normalize the path relative to the base
        $path = $this->normalizePath($path);

        // make sure it exists if set
        $realpath = $this->getRealpathAndThrowIfNotExists($path);

        // if exceptions aren't being thrown then just stop here
        if (!$realpath) {
            return $this;
        }

        // ensure the path can contain multiple suffixes
        if (!isset($this->paths[$realpath])) {
            $this->paths[$realpath] = array();
        }
        
        // add it ensuring that no duplicates are added
        $this->paths[$realpath] = array_merge($this->paths[$realpath], (array) $suffix);
        
        return $this;
    }

    /**
     * Adds multiple paths. If specifying a suffix, the path is the key and suffix is the value. Otherwise the path can
     * be supplied as the value.
     * 
     * @param array $paths The paths to add.
     * 
     * @return Locator
     */
    public function addPaths(array $paths)
    {
        foreach ($paths as $path => $suffix) {
            if (is_numeric($path)) {
                $path   = $suffix;
                $suffix = self::DEFAULT_SUFFIX;
            }
            $this->addPath($path, $suffix);
        }
        return $this;
    }
    
    /**
     * Adds the include path to PHP's include paths.
     * 
     * @param string $path The path to add to PHP's include paths.
     * 
     * @return Locator
     */
    public function addIncludePath($path)
    {
        $path     = $this->normalizePath($path);
        $realpath = $this->getRealpathAndThrowIfNotExists($path);
        
        if ($realpath) {
            set_include_path(get_include_path() . PATH_SEPARATOR . $realpath);
        }
        
        return $this;
    }

    /**
     * Adds an array of paths to PHP include paths.
     * 
     * @param array $paths The paths to add to PHP's include paths.
     * 
     * @return Locator
     */
    public function addIncludePaths(array $paths)
    {
        foreach ($paths as $path => $suffix) {
            if (is_numeric($path)) {
                $path = $suffix;
            }
            
            $this->addIncludePath($path);
        }
        
        return $this;
    }

    /**
     * Normalizes the specified path using the base path if it was specified.
     * 
     * @param string $path The path to normalize.
     * 
     * @return string
     */
    private function normalizePath($path)
    {
        return $this->base ? $this->base . DIRECTORY_SEPARATOR . $path : $path;
    }
    
    /**
     * Returns the realpath of the passed in $path. If the path does not exist, then throw an exception.
     * 
     * @throws Exception If the path does not exist.
     * 
     * @param string $path The path to check and return the real path to.
     * 
     * @return bool | string
     */
    private function getRealpathAndThrowIfNotExists($path)
    {
        if ($realpath = realpath($path)) {
            return $realpath;
        }
        
        if ($this->throwWhenAdding) {
            throw new LogicException(sprintf('The path "%s" does not exist.', $path));
        }
        
        return false;
    }
}