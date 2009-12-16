<?php

/**
 * @file
 * 
 * @package    Europa
 * @subpackage Registry
 */

/**
 * @class
 * 
 * @name Europa_Registry
 * @desc Sets variables to be stored in a common place and accessible from anywhere. Variable namespacing can be
 *       done any way you want, but it is recommended to follow a strict convention when doing so.
 */
class Europa_Registry
{
	static private 
		/**
		 * @property
		 * @static
		 * @private
		 * 
		 * @name _registry
		 * @desc Holds all registry variables.
		 */
		$_registry = array();
	
	
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name get
	 * @desc Retrieves a variable from the registry.
	 * 
	 * @param $key String - The registry variable to retrieve.
	 * 
	 * @return Mixed
	 */
	static public function get($key) {
		return self::$_registry[$key];
	}
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name set
	 * @desc Sets a variable named $key with the value of $val inside of $namespace.
	 * 
	 * @param $key String - The name of the registry variable.
	 * @param $val Mixed  - The value of the registry variable.
	 * 
	 * @return Mixed
	 */
	static public function set($key, $val) {
		self::$_registry[$key] = $val;
		
		return $val;
	}
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name remove
	 * @desc Removes the variable named $key from $namespace returning the value
	 *       of the variable that was removed.
	 * 
	 * @param $key String - The name of the registry variable to remove.
	 * 
	 * @return Mixed
	 */
	static public function remove($key) {
		$val = self::$_registry[$key];
		
		unset(self::$_registry[$key]);
		
		return $val;
	}
}