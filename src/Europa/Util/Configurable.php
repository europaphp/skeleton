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
     * Configuration array.
     * 
     * @var array
     */
    private $config = [];
    
    /**
     * Default configuration array for all instances.
     * 
     * @var array
     */
    private static $defaultConfigs = [];
    
    /**
     * Initialises the configuration.
     * 
     * @param array $config The configuration to merge with the defaults.
     * 
     * @return Configurable
     */
    public function initConfig(array $config = [])
    {
        // if a default configuration property is given, use it
        if (isset($this->defaultConfig)) {
            $this->setConfig($this->defaultConfig);
        }
        
        // if a default configuration was set using `setDefaultConfig()`, use it next
        $this->setConfig(self::$defaultConfigs[self::ensureDefaultConfigArray()]);
        
        // finally use the passed in config
        $this->setConfig($config);
        
        return $this;
    }
    
    /**
     * Sets a configuration value or multiple configuration values.
     * 
     * @param mixed $name  The name or config array.
     * @param mixed $value The value or null if passing an array as the first argument.
     * 
     * @return Configurable
     */
    public function setConfig($name, $value = null)
    {
        self::setConfigArray($this->config, $name, $value);
        return $this;
    }
    
    /**
     * Returns a configuration value.
     * 
     * @param string $name The name of the value to return. If not specified, the whole configuration is returned.
     * 
     * @return mixed
     */
    public function getConfig($name = null)
    {
        return self::getConfigArray($this->config, $name);
    }
    
    /**
     * Sets a default configuration value or multiple default configuration values.
     * 
     * @param mixed $name  The name or config array.
     * @param mixed $value The value or null if passing an array as the first argument.
     * 
     * @return void
     */
    public static function setDefaultConfig($name, $value = null)
    {
        self::setConfigArray(self::$defaultConfigs[self::ensureDefaultConfigArray()], $name, $value);
    }
    
    /**
     * Returns a default configuration value.
     * 
     * @param string $name The name of the value to return. If not specified, the whole configuration is returned.
     * 
     * @return mixed
     */
    public static function getDefaultConfig($name = null)
    {
        return self::getConfigArray(self::getDefaultConfigArray(), $name);
    }
    
    /**
     * Modifies a configuration array. Used for specific and default configurations.
     * 
     * @param &array $config The configuration array to modify.
     * @param mxied  $name   The name.
     * @param mixed  $value  The value.
     * 
     * @return void
     */
    private static function setConfigArray(array &$config, $name, $value = null)
    {
        // allow an array
        if (is_array($name)) {
            $config = array_merge($config, $name);
            return;
        }
        
        // allow traversable
        if ($name instanceof Traversable) {
            static::setConfigArray($config, iterator_to_array($name));
            return;
        }
        
        // allow an object
        if (is_object($name)) {
            static::setConfigArray((array) $config, $name);
            return;
        }
        
        // scalar
        $config[$name] = $value;
    }
    
    /**
     * Returns the config value or the whole config array if no value is passed.
     * 
     * @param array  $config The config array.
     * @param string $name   The name of the value.
     * 
     * @return mixed
     */
    private static function getConfigArray(array $config, $name = null)
    {
        // return whole config array
        if (!$name) {
            return $config;
        }
        
        // return specified value
        if (isset($config[$name])) {
            return $config[$name];
        }
    }
    
    /**
     * Ensures that there is a default configuration for the class and returns the class name.
     * 
     * @return string
     */
    private static function ensureDefaultConfigArray()
    {
        $class = get_called_class();
        if (!isset(self::$defaultConfigs[$class])) {
            self::$defaultConfigs[$class] = [];
        }
        return $class;
    }
    
    
    /**
     * Ensures that there is a default configuration for the class.
     * 
     * @return void
     */
    private static function getDefaultConfigArray()
    {
        return self::$defaultConfigs[self::ensureDefaultConfigArray()];
    }
}