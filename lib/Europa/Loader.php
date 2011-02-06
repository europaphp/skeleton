<?php

namespace Europa;

/**
 * Handles class loading in Europa. Uses custom load paths due to the
 * immense performance gain and ease of manipulation.
 * 
 * @category Loading
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Loader
{
    /**
     * Contains all load paths that Europa\RouteLoader will use when searching for a
     * file.
     * 
     * @var array
     */
    protected static $paths = array();

    protected static $separators = array('_', '\\');
    
    /**
     * Searches for a file and loads it if it is found.
     * 
     * @param string $className The Class to search for.
     * @param mixed  $paths     Alternate search paths to search in first.
     * 
     * @return bool
     */
    public static function load($file, $paths = null)
    {
        if ($file = self::search($file, $paths)) {
            include $file;
            return true;
        }
        return false;
    }
    
    /**
     * Searches for a class and loads it if it is found.
     * 
     * @param string $className The Class to search for.
     * @param mixed  $paths     Alternate search paths to search in first.
     * 
     * @return bool
     */
    public static function loadClass($className, $paths = null)
    {
        // if the class already exists, then we don't need to load it
        if (class_exists($className, false)) {
            return true;
        }
        
        // format the classname to a file
        $file = str_replace(self::$separators, DIRECTORY_SEPARATOR, $className);
        $file = $file . '.php';
        if (self::load($file, $paths) && class_exists($className, false)) {
            return true;
        }
        return false;
    }
    
    /**
     * Searches for a file and returns it's path if it is found.
     * 
     * @param string $file  The file to load, relative to the search paths.
     * @param mixed  $paths Alternate load paths to search in first.
     * 
     * @return mixed
     */
    public static function search($file, $paths = null)
    {
        // make use of specified paths, but fall back to default paths
        if ($paths) {
            $paths = array_merge((array) $paths, self::$paths);
        } else {
            $paths = self::$paths;
        }
        
        // a path must be defined
        if (!$paths) {
            // we require the exception files here since they won't be autoloadable
            require_once realpath(dirname(__FILE__) . '/Exception.php');
            require_once realpath(dirname(__FILE__) . '/Loader/Exception.php');
            throw new Loader\Exception(
                'At least one load path must be defined.',
                Loader\Exception::NOpaths_DEFINED
            );
        }
        
        // search in all paths and return the fullpath if found
        foreach ($paths as $path) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $file;
            if (is_file($fullPath)) {
                return $fullPath;
            }
        }
        return false;
    }
    
    /**
     * Searches for the specified class and returns the file if found or false
     * if not found.
     * 
     * @param string $class The class to search for.
     * @param mixed  $paths Alternate load paths to search in first.
     * 
     * @return bool|string
     */
    public static function searchClass($class, $paths = null)
    {
        $file = str_replace(self::$separators, DIRECTORY_SEPARATOR, $class) . '.php';
        return self::search($file, $paths);
    }
    
    /**
     * Adds a path to the load paths. Uses realpath to determine path validity.
     * If the path is unable to be resolve, an exception is thrown.
     * 
     * @param string $path The path to add to the list of load paths.
     * 
     * @return mixed
     */
    public static function addPath($path)
    {
        $realpath = realpath($path);

        // the path won't be added if it doesn't exist
        if (!$realpath) {
            // we require the exception files here since they may not be autoloadable yet
            require_once realpath(dirname(__FILE__) . '/Exception.php');
            require_once realpath(dirname(__FILE__) . '/Loader/Exception.php');
            throw new Loader\Exception(
                'Path ' . $path . ' does not exist.',
                Loader\Exception::INVALID_PATH
            );
        }
        self::$paths[] = $realpath;
    }
    
    /**
     * Registers the auto-load handler and automatically registers the
     * Europa install path to the load paths.
     * 
     * @param mixed $callback The custom callback function to register, if any. If not
     *                        specified, then it defaults to "loadClass".
     * 
     * @return void
     */
    public static function registerAutoload($callback = null)
    {
        if (!$callback) {
            $callback = array('\Europa\Loader', 'loadClass');
        }
        spl_autoload_register($callback);
        self::addPath(dirname(__FILE__) . '/../');
    }
}