<?php

namespace Europa\Fs;

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
     * Maps the specified item to the specified file. Also takes an array of $class to
     * $file mappings as the first argument.
     * 
     * @param string $map  The class to map, or array of $class => $file mapping.
     * @param string $file The file to map to if the first argument is not an array.
     * 
     * @return \Europa\Loader
     */
    public function map($map, $file)
    {
        if (!is_array($map)) {
            $map = array($map => $file);
        }
        foreach ($map as $item => $file) {
            $this->map[$item] = $file;
        }
        return $this;
    }
    
    /**
     * Returns the class mapping.
     * 
     * @return array
     */
    public function getMapping()
    {
        return $this->map;
    }
    
    /**
     * Searches for the specified file and returns the file if found or false if not found. If the class is found, it
     * is cached in the class map.
     * 
     * @param string $class The class to search for.
     * 
     * @return bool|string
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
        
        return false;
    }
    
    /**
     * Adds a path to the load paths. Uses realpath to determine path validity.
     * If the path is unable to be resolve, an exception is thrown.
     * 
     * @param string $path              The path to add to the list of load paths.
     * @param bool   $addToIncludePaths Whether or not to add it to PHP's include paths.
     * 
     * @throws \Europa\Loader\Exception If the path does not exist.
     * 
     * @return \Europa\Loader
     */
    public function addPath($path, $suffix = self::DEFAULT_SUFFIX)
    {
        $realpath = $this->getRealpathAndThrowIfNotExists($path);

        // ensure the path can contain multiple suffixes
        if (!isset($this->paths[$realpath])) {
            $this->paths[$realpath] = array();
        }
        
        $this->paths[$realpath] = array_merge($this->paths[$realpath], (array) $suffix);
        return $this;
    }
    
    /**
     * Adds the include path to PHP's include paths.
     * 
     * @param string $path The path to add to PHP's include paths.
     * 
     * @return \Europa\Loader
     */
    public function addIncludePath($path)
    {
        $realpath = $this->getRealpathAndThrowIfNotExists($path);
        set_include_path(get_include_path() . PATH_SEPARATOR . $realpath);
        return $this;
    }
    
    /**
     * Returns the realpath of the passed in $path. If the path does not exist, then throw an exception.
     * 
     * @throws Exception If the path does not exist.
     * 
     * @param string $path The path to check and return the real path to.
     * 
     * @return string
     */
    private function getRealpathAndThrowIfNotExists($path)
    {
        if ($realpath = realpath($path)) {
            return $realpath;
        }
        
        // we require the exception files here since they may not be autoloadable yet
        require_once realpath(dirname(__FILE__) . '/../Exception.php');
        require_once realpath(dirname(__FILE__) . '/Exception.php');
        throw new Exception("Path {$path} does not exist.");
    }
}