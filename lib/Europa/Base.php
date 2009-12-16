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
		$_config = array(),
		
		/**
		 * @property
		 * @protected
		 * 
		 * @name _instanceName
		 * @desc Contains the name of the current instance.
		 */
		$_instanceName = null;
	
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
	 * @name        __construct
	 * @desc Sets configuration, if any and an instance. If an instance with
	 *       the same name already exists, then it is overwritten.
	 * 
	 * @param Array            $config       - A configuration Array. Can also be set to null if no Configuration is used.
	 * @param String[Optional] $instanceName - The name to give this instance so that it may be retrieved using 
	 *                                         Europa_Base::getInstance($instanceName).
	 * 
	 * @return Object of child class
	 */
	public function __construct($config = null, $instanceName = 'default')
	{
		self::$_instances[$instanceName] = $this;
		$this->_instanceName             = $instanceName;
		
		$this->setConfig($config);
		
		return $this;
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
		if (is_string($config)) {
			$config = array($config => $val);
		} elseif (is_array($config)) {
			foreach ($config as $k => $v) {
				$this->_config[$k] = $v;
			}
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
	 * @param String $name - Name of the configuration variable to retrieve.
	 * 
	 * @return Mixed
	 */
	public function getConfig($name)
	{
		return $this->_config[$name];
	}
	
	/**
	 * @method
	 * @public
	 * 
	 * @name getInstanceName
	 * @desc Retrives a the instance name of the current class.
	 * 
	 * @return String
	 */
	public function getInstanceName()
	{
		return $this->_instanceName;
	}
	
	
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name getInstance
	 * @desc Retrieves a named instance of the child class. Instances are cached in Europa_Base, but after PHP 5.3.0 instances will be
	 *       cached on the child class using late static binding. This means that, before PHP 5.3.0, you cannot have a instance called 
	 *       'default' on Europa_Controller as well as Europa_View, otherwise one will be overwritten. If you are using PHP 5.3.0 you can.
	 * 
	 * @param String[Optional] $name - The optional name of the instance to get. Defaults to 'default'.
	 * 
	 * @return Object Child Class
	 */
	static public function getInstance($name = 'default')
	{
		// If an instance doesn't exist, create one
		if (!isset(self::$_instances[$name])) {
			self::$_instances[$name] = new self($name);
		}
		
		return self::$_instances[$name];
	}
}