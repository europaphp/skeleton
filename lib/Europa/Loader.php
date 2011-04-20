<?php

namespace Europa;
use Europa\Loader\Exception;

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
     * The default file suffix.
     * 
     * @var string
     */
    const SUFFIX = 'php';
    
    /**
     * Contains all load paths that Europa\RouteLoader will use when searching for a file.
     * 
     * @var array
     */
    private static $paths = array();

    /**
     * The separators used for namespaces.
     * 
     * @var array
     */
    private static $separators = array('_', '\\', '/');
    
    /**
     * Maps classes to their absolute paths.
     * 
     * @var array
     */
    private static $map = array();
    
    /**
     * Maps the specified class to the specified file. Also takes an array of $class to
     * $file mappings as the first argument.
     * 
     * @param string $map  The class to map, or array of $class => $file mapping.
     * @param string $file The file to map to if the first argument is not an array.
     * 
     * @return \Europa\Loader
     */
    public static function map($map, $file)
    {
        if (!is_array($map)) {
            $map = array($map => $file);
        }
        
        foreach ($map as $class => $file) {
            self::$map[$class] = $file;
        }
    }
    
    /**
     * Returns the class mapping.
     * 
     * @return array
     */
    public static function getMapping()
    {
        return self::$map;
    }
    
    /**
     * Searches for a class and loads it if it is found. If the class is found, it true
     * is returned. Otherwise false is returned.
     * 
     * @param string $class The class to search for.
     * 
     * @return bool
     */
    public static function load($class)
    {
        if (class_exists($class, false)) {
            return true;
        }
        
        if ($file = self::search($class)) {
            require_once $file;
            return true;
        }
        
        return false;
    }
    
    /**
     * Searches for the specified class and returns the file if found or false if not found.
     * If the class is found, it is cached in the class map.
     * 
     * @param string $class The class to search for.
     * 
     * @return bool|string
     */
    public static function search($class)
    {
        if (isset(self::$map[$class])) {
            return self::$map[$class];
        }
        
        $file = str_replace(self::$separators, DIRECTORY_SEPARATOR, $class);
        $file = trim($file, DIRECTORY_SEPARATOR);
        foreach (self::$paths as $path => $suffixes) {
            $fullpath = $path . DIRECTORY_SEPARATOR . $file . '.php';
            if (file_exists($fullpath)) {
                self::$map[$class] = $fullpath;
                return $fullpath;
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
    public static function addPath($path, $addToIncludePaths = false)
    {
        $realpath = realpath($path);

        // the path won't be added if it doesn't exist
        if (!$realpath) {
            // we require the exception files here since they may not be autoloadable yet
            require_once realpath(dirname(__FILE__) . '/Exception.php');
            require_once realpath(dirname(__FILE__) . '/Loader/Exception.php');
            throw new Exception(
                'Path ' . $path . ' does not exist.',
                Exception::INVALID_PATH
            );
        }

        self::$paths[$realpath] = $realpath;
        if ($addToIncludePaths) {
            set_include_path(get_include_path() . PATH_SEPARATOR . $realpath);
        }
    }
    
    /**
     * Registers the auto-load handler and automatically registers the
     * Europa install path to the load paths.
     * 
     * @return \Europa\Loader
     */
    public static function register()
    {
        spl_autoload_register(array('\Europa\Loader', 'load'));
        self::addPath(dirname(__FILE__) . '/../');
    }
}