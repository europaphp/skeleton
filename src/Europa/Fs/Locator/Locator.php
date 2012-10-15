<?php

namespace Europa\Fs\Locator;
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
class Locator
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
     * Searches for the specified file and returns the file path if found.
     * 
     * @param string $class The class to search for.
     * 
     * @return bool | string
     */
    public function __invoke($file)
    {
        // normalize
        $file = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $file);
        $file = trim($file, DIRECTORY_SEPARATOR);
        
        // if it exists in the map just return it
        if (isset($this->map[$file])) {
            return $this->map[$file];
        }

        // first check against the base path
        if ($fullpath = realpath($this->base . '/' . $file . '.' . self::DEFAULT_SUFFIX)) {
            return $fullpath;
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
    }

    /**
     * Allows a base path to be supplied which all paths should be specified relative to.
     * 
     * @param string $base The base path.
     * @param bool   $test Check if the path exists.
     * 
     * @return Locator
     */
    public function setBasePath($base, $test = true)
    {
        $real = realpath($base);

        if ($test && !$real) {
            throw new InvalidArgumentException(sprintf('The base path "%s" does not exist.', $base));
        }

        $this->base = $real;
        
        return $this;
    }

    /**
     * Returns the base path that was set.
     * 
     * @return string
     */
    public function getBasePath()
    {
        return $this->base;
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
     * Adds a path to the load paths. Uses realpath to determine path validity. If the path is unable to be resolve, an
     * exception is thrown if throwing is turned on.
     * 
     * @param string $path   The path to add to the list of load paths.
     * @param mixed  $suffix The suffix, or suffixes to use for this path.
     * @param bool   $test   Check if path exists.
     * 
     * @return Locator
     */
    public function addPath($path, $suffix = self::DEFAULT_SUFFIX, $test = true)
    {
        // normalize the path relative to the base
        $real = realpath($this->base ? $this->base . DIRECTORY_SEPARATOR . $path : $path);

        if ($test && !$real) {
            throw new LogicException(sprintf('The path "%s" does not exist.', $real));
        }

        // ensure the path can contain multiple suffixes
        if (!isset($this->paths[$real])) {
            $this->paths[$real] = array();
        }
        
        // add it ensuring that no duplicates are added
        $this->paths[$real] = array_merge($this->paths[$real], (array) $suffix);
        
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
    public function addPaths(array $paths, $test = true)
    {
        foreach ($paths as $path => $suffix) {
            if (is_numeric($path)) {
                $path   = $suffix;
                $suffix = self::DEFAULT_SUFFIX;
            }

            $this->addPath($path, $suffix, $test);
        }

        return $this;
    }
}