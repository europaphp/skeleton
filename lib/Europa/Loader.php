<?php

/**
 * Handles class loading in Europa. Uses custom load paths due to the
 * immense performance gain and ease of manipulation.
 * 
 * @category Loading
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Loader
{
	/**
	 * Contains all load paths that Europa_Loader will use when searching for a
	 * file.
	 * 
	 * @var array
	 */
	protected static $_paths = array();
	
	/**
	 * Searches for a class and loads it if it is found.
	 * 
	 * @param string $className The Class to search for.
	 * @param mixed $paths Alternate search paths to search in first.
	 * @return bool
	 */
	public static function loadClass($className, $paths = null)
	{
		// if the class already exists, then we don't need to load it
		if (class_exists($className, false)) {
			return true;
		}
		
		// format the classname to a file
		$file = str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $className);
		if (self::load($file, $paths) && class_exists($className, false)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Searches for a file and loads it if it is found.
	 * 
	 * @param string $className The Class to search for.
	 * @param mixed $paths Alternate search paths to search in first.
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
	 * Searches for a file and returns it's path if it is found.
	 * 
	 * @param string $file The file to load, relative to the search paths.
	 * @param mixed $paths Alternate load paths to search in first.
	 * @return string|false
	 */
	public static function search($file, $paths = null)
	{
		// make use of specified paths, but fall back to default paths
		if ($paths) {
			$paths = array_merge((array) $paths, self::$_paths);
		} else {
			$paths = self::$_paths;
		}
		
		// a path must be defined
		if (!$paths) {
			// we require the exception files here since they won't be autoloadable
			require_once realpath(dirname(__FILE__) . '/Exception.php');
			require_once realpath(dirname(__FILE__) . '/Loader/Exception.php');
			throw new Europa_Loader_Exception(
				'At least one load path must be defined.',
				Europa_Loader_Exception::NO_PATHS_DEFINED
			);
		}
		
		// search in all paths and return the fullpath if found
		foreach ($paths as $path) {
			$fullPath = $path . DIRECTORY_SEPARATOR . $file . '.php';
			if (is_file($fullPath)) {
				return $fullPath;
			}
		}
		return false;
	}
	
	/**
	 * Adds a path to the load paths. Uses realpath to determine path validity.
	 * If the path is unable to be resolve, an exception is thrown.
	 * 
	 * @param string $path The path to add to the list of load paths.
	 * @return bool|string
	 */
	public static function addPath($path)
	{
		$realpath = realpath($path);
		// the path won't be added if it doesn't exist
		if (!$realpath) {
			// we require the exception files here since they may not be
			// autoloadable yet
			require_once realpath(dirname(__FILE__) . '/Exception.php');
			require_once realpath(dirname(__FILE__) . '/Loader/Exception.php');
			throw new Europa_Loader_Exception(
				'Path ' . $path . ' does not exist.',
				Europa_Loader_Exception::INVALID_PATH
			);
		}
		self::$_paths[] = $realpath;
	}
	
	/**
	 * Registers the auto-load handler.
	 * 
	 * @return void
	 */
	public static function registerAutoload()
	{
		spl_autoload_register(array('Europa_Loader', 'loadClass'));
	}
}