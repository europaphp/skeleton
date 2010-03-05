<?php

/**
 * @author Trey Shugart
 */

/**
 * Handles class loading in Europa. Uses custom load paths due to the
 * immense performance gain and ease of manipulation.
 * 
 * @package Europa
 * @subpackage Loader
 */
class Europa_Loader
{
	/**
	 * Contains logging information.
	 * 
	 * @var boolean
	 */
	protected static $log = array();
	
	/**
	 * Contains all load paths that Europa_Loader will use when attempting 
	 * to load a class.
	 * 
	 * @var array
	 */
	protected static $paths = array();
	
	/**
	 * Loads a class based on the Europa file naming convention. If the class
	 * is found, it's full path is returned. If not, then false is returned.
	 * 
	 * Alternate load paths can be specified to search in before the default
	 * load paths in an explicit call to loadClass.
	 * 
	 * @param string $className The Class to load.
	 * @param string|array $paths Alternate load paths to search in first.
	 * @return bool|string
	 */
	public static function loadClass($className, $paths = null)
	{
		// if the class already exists, then we don't need to load it
		if (class_exists($className, false)) {
			return true;
		}
		
		// for logging purposes
		$startTime = microtime();
		
		// Parse the class name to find the conventional file path relative to
		// either the passed $path or the load paths.
		$file  = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		$found = false;
		
		// make use of specified paths, but fall back to set paths
		if ($paths) {
			$paths = array_merge((array) $paths, self::$paths);
		}
		// if not just use original paths
		else {
			$paths = self::$paths;
		}
		
		// if there aren't any paths, die with a message
		if (!$paths) {
			die('
				No load paths are defined. Please define a load path using
				Europa_Loader::addPath(\'./path/to/files\').
			');
		}
		
		// search for the file
		foreach ($paths as $path) {
			$fullPath = $path . DIRECTORY_SEPARATOR . $file;
			
			// break if the file is found
			if (is_file($fullPath)) {
				$found = true;
				
				break;
			}
		}
		
		// if found, include
		if ($found) {
			include $fullPath;
		}
		
		// build logging information
		self::$log[] = array(
			'class' => $className,
			'path'  => $fullPath,
			'time'  => microtime() - $startTime,
			'found' => $found
		);
		
		if ($found) {
			return $fullPath;
		}
		
		return false;
	}
	
	/**
	 * If path exists it is added and returned, otherwise false is returned.
	 * 
	 * Using load paths in this manner, is far faster than using PHP's built-in
	 * include paths.
	 * 
	 * @param string $path The path to add to the list of load paths.
	 * @return bool|string
	 */
	public static function addPath($path)
	{
		$path = realpath($path);
		
		// the path won't be added if it doesn't exist
		if (!$path) {
			return false;
		}
		
		self::$paths[] = $path;
		
		return $path;
	}
	
	/**
	 * Returns an array of all bound load paths.
	 * 
	 * @return array
	 */
	public static function getPaths()
	{
		return self::$paths;
	}
	
	/**
	 * Returns a log with information about loaded classes.
	 * 
	 * @return array
	 */
	public static function getLog()
	{
		return self::$log;
	}
	
	/**
	 * Registers the auto-load handler. This first looks to see if the
	 * spl_autoload_register function exists. If so, it is utilized, if not,
	 * then it falls back to __autoload.
	 * 
	 * @return void
	 */
	public static function registerAutoload()
	{
		if (function_exists('spl_autoload_register')) {
			spl_autoload_register(array('Europa_Loader', 'loadClass'));
		} else {
			function __autoload($className)
			{
				Europa_Loader::loadClass($className);
			}
		}
	}
}
