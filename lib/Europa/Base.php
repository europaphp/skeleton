<?php

/**
 * @file
 * 
 * @package    Europa
 * @subpackage Base
 */

/**
 * @class
 * 
 * @name Europa_Base
 * @desc Provides an abstraction for classes following a common design pattern used in 
 *       Europa which handles multiple instances, configuration and method/property 
 *       setting.
 */
abstract class Europa_Base
{
	protected
		/**
		 * @property
		 * @protected
		 * 
		 * @name _config
		 * @desc Contains configuration variables defined by the child class.
		 */
		$_config = array();
	
	static protected
		/**
		 * @property
		 * @static
		 * @protected
		 * 
		 * @name _instances
		 * @desc Contains all of the instances of the child class.
		 */
		$_instances = array();
	
	
	
	/**
	 * @method
	 * @magic
	 * 
	 * @name __construct
	 * @desc Sets configuration, if any and an instance. If an instance with
	 *       the same name already exists, then it is overwritten.
	 * 
	 * @param Array $config - A configuration Array. Can also be set to null if no Configuration is used.
	 * 
	 * @return Object
	 */
	public function __construct($config = null)
	{
		return $this->setConfig($config);
	}
	
	
	
	/**
	 * @method
	 * @public
	 * 
	 * @name setConfig
	 * @desc Sets one or more configuration variables.
	 * 
	 * @param Mixed $config        - Can be a string or Array of key value pairs. If a string is given,
	 *                               $val is used as the value and this as the key.
	 * @param Mixed[Optional] $val - If $config is an Array, then $val is ignored and defaults to null if not specified.
	 * 
	 * @return Mixed
	 */
	public function setConfig($config, $val = null)
	{
		// if the first argument is a string, normalize to an array
		if (is_string($config)) {
			$config = array($config => $val);
		}
		
		// $config can be a stdClass
		$config = (array) $config;
		
		foreach ($config as $k => $v) {
			$this->_config[$k] = $v;
		}
		
		return $this;
	}
	
	/**
	 * @method
	 * @public
	 * 
	 * @name getConfig
	 * @desc Retrives a configuration variable.
	 * 
	 * @param Mixed $name - Name of the configuration variable to retrieve. If no name is specified,
	 *                      The whole config array is returned.
	 * 
	 * @return Mixed
	 */
	public function getConfig($name = null)
	{
		if ($name) {
			return $this->_config[$name];
		}
		
		return $this->_config;
	}
	
	/**
	 * @method
	 * @public
	 * 
	 * @name setInstance
	 * @desc Saves the current instance given a name so it can be retrieved later.
	 * 
	 * @param String $name - The alias to give the instance you are saving.
	 * 
	 * @return Object
	 */
	public function setInstance($name) {
		self::$_instances[$name] = $this;
		
		return $this;
	}
	
	
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name getInstance
	 * @desc Retrieves a named instance of the class.  All instances are saved to Europa_Base. 
	 *       The behavior may change in the future using late static binding and PHP 5.3.0 when 
	 *       requiring 5.3.0 is safe.
	 * 
	 * @param String $name - The name of the instance to get.
	 * 
	 * @return Object
	 */
	static public function getInstance($name)
	{
		return self::$_instances[$name];
	}
}