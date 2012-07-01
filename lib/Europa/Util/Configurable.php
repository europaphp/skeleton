<?php

namespace Europa\Util;
use Traversable;

/**
 * A trait that enables an object to be configurable. This normally will be used in lieu of passing dependencies as
 * arguments to a constructor.
 * 
 * @category Utilities
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
trait Configurable
{
	/**
	 * Configuration array. Can be overridden to give defaults.
	 * 
	 * @var array
	 */
	protected $config = [];
	
	/**
	 * Constructs a new configuration object.
	 * 
	 * @param mixed $config Can be an array or object.
	 * 
	 * @return Configurable
	 */
	public function __construct($config = [])
	{
		// allow an array
		if (is_array($name)) {
			$this->config = array_merge($this->config, $name);
			return $this;
		}
		
		// allow traversable
		if ($name instanceof Traversable) {
			return $this->setConfig(iterator_to_array($name));
		}
		
		// allow an object
		if (is_object($name)) {
			return $this->setConfig((array) $name);
		}
		
		// scalar
		$this->config[$name] = $value;
	}
	
	/**
	 * Returns a configuration value.
	 * 
	 * @param string $name The name of the value to return. If not specified, the whole configuration is returned.
	 * 
	 * @return mixed
	 */
	public function config($name = null)
	{
		// return whole config array
		if (!$name) {
			return $this->config;
		}
		
		// return specified value
		if (isset($this->config[$name])) {
			return $this->config[$name];
		}
	}
}