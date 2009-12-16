<?php

/**
 * @file
 * 
 * @package    Europa
 * @subpackage Loader
 */

/**
 * @class
 * 
 * @name Europa_Loader
 * @desc Handles all loading done in Europa using custom load paths so it doesn't interfere with PHP's default load paths.
 *       You can, however, use PHP's include paths if you desire, as well as a custom load path. See: Europa_Loader::loadClass().
 */
class Europa_Loader
{
	static public
		/**
		 * Contains logging information.
		 * 
		 * @var boolean
		 */
		$log = array();
	
	static private 
		/**
		 * @property
		 * @static
		 * @private
		 * 
		 * @name _loadPaths
		 * @desc Contains all load paths that Europa_Loader will use when attempting to load a class.
		 */
		$_loadPaths = array();
	
	/**
	 * Loads a class based on the Europa file naming convention and returns it. 
	 * If the class is unable to be loaded, false is returned.
	 * 
	 * @param string $className The Class to load.
	 * 
	 * @return bool|string
	 */
	static public function loadClass($className, $path = null)
	{
		// if the class already exists, then we don't need to load it
		if (class_exists($className, false)) {
			return true;
		}
		
		// for logging purposes
		$startTime = microtime();
		
		// Parse the class name to find the conventional file path relative to either
		// the passed $path or the load paths.
		$file  = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		$found = false;
		$paths = $path
			? array_merge((array) $path, self::$_loadPaths)
			: self::$_loadPaths;
		
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
		
		// build the log
		self::$log[] = array(
			'class' => $className,
			'path'  => $fullPath,
			'time'  => microtime() - $startTime,
			'found' => $found
		);
		
		return $found ? $fullPath : false;
	}
	
	/**
	 * If path exists it is added and returned, otherwise false is returned.
	 * 
	 * @param string $path The path to add to the list of load paths.
	 * 
	 * @return bool|string
	 */
	static public function addLoadPath($path)
	{
		$path = realpath($path);
		
		// the path won't be added if it doesn't exist
		if (!$path) {
			return false;
		}
		
		self::$_loadPaths[$path] = $path;
		
		return $path;
	}
	
	/**
	 * If path exists in the load paths it is removed and returned, otherwise false is returned.
	 * 
	 * @param $name string The name of the path given at the time of adding. If no name was given, 
	 *                     it attempts to find via the path value.
	 * 
	 * @return bool|string
	 */
	static public function removeLoadPath($path) {
		if (isset(self::$_loadPaths[$path])) {
			unset(self::$_loadPaths[$path]);
			
			return $path;
		}
		
		return false;
	}
}
