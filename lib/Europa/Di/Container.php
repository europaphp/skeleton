<?php

namespace Europa\Di;

/**
 * Dependency injection container.
 * 
 * @category DependencyInjection
 * @package  Europa\Di
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Container
{
    /**
     * The default container instance name.
     * 
     * @var string
     */
    const DEFAULT_INSTANCE_NAME = 'default';
    
    /**
     * Preset configuration for dependencies.
     * 
     * @var array
     */
    private $config = array();
    
    /**
     * Cached dependency instances.
     * 
     * @var array
     */
    private $deps = array();
    
    /**
     * Formatter callback for formatting dependency names into class names.
     * 
     * @var mixed
     */
    private $formatter;
    
    /**
     * Mapping of name => className for dependencies.
     * 
     * @var array
     */
    private $map = array();
    
    /**
     * Preset method queue for dependencies.
     * 
     * @var array
     */
    private $queue = array();
    
    /**
     * Container instances for static retrieval.
     * 
     * @var array
     */
    private static $containers = array();
    
    /**
     * Sets up a new dependency.
     * 
     * @param string $name The name of the dependency.
     * @param array  $args The arguments for the dependency constructor.
     * 
     * @return \Europa\Di\Dependency
     */
    public function __call($name, array $args = array())
    {
        if (!isset($this->deps[$name])) {
            $dep = $this->getClassNameFor($name);
            $dep = new Dependency($dep);
            $this->deps[$name] = $dep;
        }
        
        if ($args) {
            $this->deps[$name]->configure($args);
        }
        
        return $this->deps[$name];
    }
    
    /**
     * Detects the value of $value and handles it appropriately.
     * - Instances of \Europa\Di\Dependency are registered on the container.
     * - Other instances are created as a dependency then registered.
     * - Strings are used as a class map for $name.
     * 
     * @param string $name  The name of the dependency.
     * @param mixed  $value One of many allowed values.
     * 
     * @return \Europa\Di\Container
     */
    public function __set($name, $value)
    {
        if ($value instanceof Dependency) {
            $this->deps[$name] = $value;
        } elseif (is_object($value)) {
            $this->deps[$name] = new Dependency(get_class($value));
            $this->deps[$name]->set($value);
        } elseif (is_string($value)) {
            $this->map[$name] = $value;
        }
        
        return $this;
    }
    
    /**
     * Returns the specified dependency.
     * 
     * @param string $name The dependency name.
     * 
     * @return \Europa\Di\Dependency
     */
    public function __get($name)
    {
        if (isset($this->deps[$name])) {
            return $this->deps[$name];
        }
        
        return $this->__call($name);
    }
    
    /**
     * Map a dependency name to a class.
     * 
     * @param string $map   An array of $map => $value or a dependency name for $value.
     * @param string $class The class to map the dependency to.
     * 
     * @return \Europa\Di\Container
     */
    public function map($map, $class = null)
    {
        if (!is_array($map)) {
            $map = array($map, $class);
        }
        
        foreach ($map as $name => $class) {
            $this->__set($name, $class);
        }
        
        return $this;
    }
    
    /**
     * Sets a formatter to use. Maps take priority and the formatter is used if a map is not defined for a given
     * dependency.
     * 
     * @param mixed $formatter A callable formatter for dependency names. The first parameter is the dependency name.
     * 
     * @return string
     */
    public function setFormatter($formatter)
    {
        if (!is_callable($formatter)) {
            throw new Exception("The specified formatter is not callable.");
        }
        
        $this->formatter = $formatter;
        return $this;
    }
    
    /**
     * Returns the class name for the specified dependency. If no map or formatter is found, the name is simply
     * returned.
     * 
     * @param string $name The name of the dependency to get the class name for.
     * 
     * @return string
     */
    private function getClassNameFor($name)
    {
        if (isset($this->map[$name])) {
            return $this->map[$name];
        } elseif ($this->formatter) {
            return call_user_func($this->formatter, $name);
        }
        
        return $name;
    }
    
    /**
     * Returns an instance of a container.
     * 
     * @param string $name The instance name to get if using multiple instances.
     * 
     * @return \Europa\Di\Container
     */
    public static function get($name = self::DEFAULT_INSTANCE_NAME)
    {
        if (!isset(static::$containers[$name])) {
            static::$containers[$name] = new static;
        }
        
        return static::$containers[$name];
    }
}
