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
	const
		/**
		 * @constant
		 * @exception
		 * 
		 * @name EXCEPTION_CLASS_NOT_FOUND
		 * @desc Gets thrown when a class is unable to be found when using Europa_Loader::loadClass(), or when attempting to autoload
		 *       a class that doesn't exist.
		 */
		EXCEPTION_CLASS_NOT_FOUND = 3;
	
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
	 * @desc Loads a class - based on the Europa naming convention - named $className and returns the full path to the file if found. Note that loadClass checks for file existence. This is because on BSD systems before 5.3.0, realpath will not return false if only the last part of the path didn't exist. In this case, that would be the name of the file. Once PHP 5.3.0 becomes mandatory, then this can be removed thus yielding a slight performance increase.
	 * 
	 * @param String           $className - The Class to load.
	 * @param String[Optional] $path      - An optional path to load from. If not specified, it will use the defined load paths. Used by autoloader.
	 * 
	 * @return Boolean - Returns true if the class was loaded, or if it already exists and false if there was an error.
	 */
	static public function loadClass($className, $path = null)
	{
		// if the class already exists, then we don't need to load it
		if (class_exists($className, false)) {
			return true;
		}
		
		// Parse the class name to find the conventional file path relative to either
		// the passed $path or the load paths.
		$file = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		
		// if a path is supplied attempt to load the class using it
		if ($path) {
			// check to see if there is a loadPath, if not assume a custom path was passed
			$path     = isset(self::$_loadPaths[$path]) ? self::$_loadPaths[$path] : $path;
			$fullPath = realpath($path . DIRECTORY_SEPARATOR . $file);
			
			if (!is_file($fullPath)) {
				$fullPath = false;
			}
		// otherwise search in the defined load paths
		} else {
			foreach (self::$_loadPaths as $path) {
				$fullPath = realpath($path . DIRECTORY_SEPARATOR . $file);
				
				// break if the file is found
				if (is_file($fullPath)) {
					break;
				} else {
					$fullPath = false;
				}
			}
		}
		
		if ($fullPath) {
			include $fullPath;
		}
		
		// returns either false or the full path to the class file
		return $fullPath
			? true
			: false;
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
			// if a class is unable to be loaded, then we must 'manually' handle the exception using the
			// static handle method because on some systems, an error saying the class cannot be found
			// is triggered before an exception can be thrown unless you echo the message in the constructor
			// of the exception class, which then limits the extensibility of the class
			if (!Europa_Loader::loadClass($className)) {
				Europa_Exception::trigger('Unable to autoload class <strong>' . $className . '</strong>.', Europa_Loader::EXCEPTION_CLASS_NOT_FOUND);
			}
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
