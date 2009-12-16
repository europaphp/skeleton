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
 *              You can, however, use PHP's include paths if you desire, as well as a custom load path. See: Europa_Loader::loadClass().
 */
class Europa_Loader
{
	const
		/**
		 * @constant
		 * @exception
		 * 
		 * @name EXCEPTION_CLASS_NOT_FOUND
		 * @desc Gets thrown when a class is unable to be found when using Europa_Loader::loadClass(), or when attempting to autoload
		 *       a class that doesn't exist.
		 */
		EXCEPTION_CLASS_NOT_FOUND = 0;
	
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
	 * @method
	 * @static
	 * @public
	 * 
	 * @name loadClass
	 * @desc Loads a class - based on the Europa naming convention - named $className and returns the name.
	 * 
	 * @param String           $className - The Class to load.
	 * @param String[Optional] $path      - An optional path to load from. If not specified, it will use the defined load paths. Used by autoloader.
	 * 
	 * @return String
	 */
	static public function loadClass($className, $path = null)
	{
		// if the class already exists, then we don't need to load it
		if (class_exists($className, false)) {
			return $className;
		}
		
		// Parse the class name to find the conventional file path relative to either
		// the passed $path or the load paths.
		$file   = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		$loaded = false;
		
		// if a path is supplied attempt to load the class using it
		if ($path) {
			$fullPath = realpath($path . DIRECTORY_SEPARATOR . $file);
			
			if (!$fullPath) {
				throw new Europa_Exception('Unable to find class <strong>' . $className . '</strong> using <strong>' . $path . '</strong>', self::EXCEPTION_CLASS_NOT_FOUND);
			}
			
			include $fullPath;
		
			return $className;
		// otherwise search in the defined load paths
		} else {
			foreach (self::$_loadPaths as $path) {
				$fullPath = realpath($path . DIRECTORY_SEPARATOR . $file);
				
				if (!$fullPath) {
					continue;	
				}
				
				include $fullPath;
				
				return $className;
			}
			
			// if a file wasn't loaded and className returned, throw an exception
			throw new Europa_Exception('Unable to find class "<strong>' . $className . '</strong>" in "<strong>' . implode(', ', self::$_loadPaths) . '</strong>".', self::EXCEPTION_CLASS_NOT_FOUND);
		}
	}
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name registerAutoload
	 * @desc Turns autoloading on and is only used internally as autoloading is always on.
	 * 
	 * @return Void
	 */
	static public function registerAutoload()
	{
		function __autoload($className)
		{
			Europa_Loader::loadClass($className);
		}
	}
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name addLoadPath
	 * @desc Adds a load path and returns it.
	 * 
	 * @param String           $path - The path to add to the list of load paths.
	 * @param String[Optional] $name - The name of the load path to use as a reference to this load path. Makes it easier to remove if necessary.
	 * 
	 * @return String
	 */
	static public function addLoadPath($path, $name = null)
	{
		// if a path name isn't given, the name is the path
		$name = $name ? $name : $path;
		$path = realpath($path);
		
		if (!$path) {
			return false;
		}
		
		self::$_loadPaths[$name] = $path;
		
		return $path;
	}
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name removeLoadPath
	 * @desc Removes a load path and returns it.
	 * 
	 * @param $name String - The name of the path given at the time of adding. If no name was given, it attempts to find via the path value.
	 * 
	 * @return String
	 */
	static public function removeLoadPath($name) {
		// the path name
		unset(self::$_loadPaths[$name]);
		
		return $path;
	}
}
