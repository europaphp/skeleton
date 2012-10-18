<?php

namespace Europa\Fs;
use ArrayIterator;
use Europa\Exception\Exception;
use IteratorAggregate;

/**
 * Handles file locating based on cascading custom paths.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Locator implements IteratorAggregate
{
    /**
     * Maps classes to their absolute paths.
     * 
     * @var array
     */
    private $cache = array();
    
    /**
     * Contains all load paths that Europa\RouteLoader will use when searching for a file.
     * 
     * @var array
     */
    private $paths = array();

    /**
     * The root path.
     * 
     * @var string
     */
    private $root;

    /**
     * Sets up the locator.
     * 
     * @param string $root The root path.
     * 
     * @return Locator
     */
    public function __construct($root = null)
    {
        if (func_num_args()) {
            $this->setRoot($root);
        }
    }

    /**
     * Searches for the specified file and returns the file path if found.
     * 
     * @param string $class The class to search for.
     * 
     * @return bool | string
     */
    public function __invoke($file)
    {
        $file = str_replace('\\', '/', $file);

        if (isset($this->cache[$file])) {
            return $this->cache[$file];
        }

        foreach ($this->paths as $path) {
            if (is_file($real = realpath($path . '/' . $file))) {
                return $this->cache[$file] = $real;
            }
        }
    }

    /**
     * Sets the root path.
     * 
     * @param string $root The root path.
     * 
     * @return Locator
     */
    public function setRoot($root)
    {
        if (!$this->root = realpath($root)) {
            Exception::toss('The root path "%s" does not exist.', $root);
        }

        return $this;
    }

    /**
     * Returns the root path.
     * 
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }
    
    /**
     * Adds a path.
     * 
     * @param string $path The path to add to the list of load paths.
     * 
     * @return Locator
     */
    public function addPath($path)
    {
        $path = $this->root ? $this->root . '/' . $path : $path;
        
        if (!$real = realpath($path)) {
            Exception::toss('The path "%s" does not exist.', $path);
        }

        $this->paths[] = $real;

        return $this;
    }

    /**
     * Adds multiple paths.
     * 
     * @param mixed $paths The paths to add.
     * 
     * @return Locator
     */
    public function addPaths($paths)
    {
        if (is_array($paths) || is_object($paths)) {
            foreach ($paths as $path) {
                $this->addPath($path);
            }
        }

        return $this;
    }

    /**
     * Returns all the paths in an array iterator.
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->paths);
    }
}