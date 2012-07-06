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
     * Initialises the configuration.
     * 
     * @param array $config The configuration to merge with the defaults.
     * 
     * @return Configurable
     */
    public function initConfig(array $config = [])
    {
        $this->restoreDefaultConfig()->setConfig($config);
    }
    
    /**
     * Sets a configuration value or multiple configuration values.
     * 
     * @param mixed $name  The name or config array.
     * @param mixed $value The value or 
     */
    public function setConfig($name, $value = null)
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
        // return whole config array
        if (!$name) {
            return $this->config;
        }
        
        // return specified value
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
    }
    
    /**
     * Returns default configuration. If a `defaultConfiguration` property is set, that is used.
     * 
     * @return array
     */
    public function getDefaultConfig()
    {
        if (isset($this->defaultConfig) && is_array($this->defaultConfig)) {
            return $this->defaultConfig;
        }
        return [];
    }
    
    /**
     * Resets the configuration back to defaults using `getDefaultConfiguration()`.
     * 
     * @return Configurable
     */
    public function setDefaultConfig(array $config)
    {
        $this->defaultConfig = $config;
        return $this;
    }
    
    /**
     * Restores the default configuration.
     * 
     * @return Configurable
     */
    public function restoreDefaultConfig()
    {
        return $this->setConfig($this->getDefaultConfig());
    }
}