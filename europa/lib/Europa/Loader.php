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
	 * Contains logging information.
	 * 
	 * @var bool
	 */
	protected static $_log = array();
	
	/**
	 * Contains all load paths that Europa_Loader will use when attempting 
	 * to load a class.
	 * 
	 * @var array
	 */
	protected static $_paths = array();
	
	/**
	 * Loads a class based on the Europa file naming convention. If the class
	 * is found, it's full path is returned. If not, then false is returned.
	 * 
	 * Alternate load paths can be specified to search in before the default
	 * load paths in an explicit call to loadClass.
	 * 
	 * @param string $className The Class to load.
	 * @param mixed $paths Alternate load paths to search in first.
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
			$paths = array_merge((array) $paths, self::$_paths);
		// if not just use original paths
		} else {
			$paths = self::$_paths;
		}
		
		// if there aren't any paths, die with a message
		if (!$paths) {
			// we require the exception files here since they won't be loadable
			require_once realpath(dirname(__FILE__) . '/Exception.php');
			require_once realpath(dirname(__FILE__) . '/Loader/Exception.php');
			throw new Europa_Loader_Exception(
				"At least one load path must be defined.",
				Europa_Loader_Exception::NO_PATHS_DEFINED
			);
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
		self::$_log[] = array(
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
	 * Returns an array of all bound load paths.
	 * 
	 * @return array
	 */
	public static function getPaths()
	{
		return self::$_paths;
	}
	
	/**
	 * Returns a log with information about loaded classes.
	 * 
	 * @return array
	 */
	public static function getLog()
	{
		return self::$_log;
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
