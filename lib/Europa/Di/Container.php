<?php

namespace Europa\Di;
use Europa\Filter\FilterInterface;

/**
 * The dependency injection container represents a collection of configured dependencies. Dependencies are instances
 * of \Europa\Di\Dependency that represent an object instance. The container provides a fluent interface for
 * accessing dependencies so that they can easily be configured.
 * 
 * Dependencies can have both a mapping, which maps a dependency name to a class name, or a formatter which will format
 * the name into a class name. The map is first checked and if it is not found it uses a formatter if one is set.
 * 
 * @category DependencyInjection
 * @package  Europa
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
     * Filter used for name formatting.
     * 
     * @var \Europa\Filter\FilterInterface
     */
    private $filter;
    
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
     * Magic caller for resolve($name, $args).
     * 
     * @see \Europa\Di\Dependency::register()
     */
    public function __call($name, array $args = array())
    {
        return $this->resolve($name)->configure($args);
    }
    
    /**
     * Magic caller for register().
     * 
     * @see \Europa\Di\Dependency::register()
     */
    public function __set($name, $value)
    {
        return $this->register($name, $value);
    }
    
    /**
     * Magic caller for resolve($name).
     * 
     * @see \Europa\Di\Dependency::resolve()
     */
    public function __get($name)
    {
        return $this->resolve($name);
    }
    
    public function __isset($name)
    {
        return $this->isRegistered($name);
    }
    
    public function __unset($naem)
    {
        return $this->unregister($name);
    }
    
    public function configure($name, array $args = array())
    {
        $this->resolve($name)->configure($args);
    }
    
    /**
     * Creates a dependency if it doesn't already exist and returns it.
     * 
     * @param string $name The name of the dependency.
     * 
     * @return \Europa\Di\Dependency
     */
    public function resolve($name)
    {
        if (!isset($this->deps[$name])) {
            $dep = $this->getClassNameFor($name);
            $dep = new Dependency($dep);
            $this->deps[$name] = $dep;
        }
        return $this->deps[$name];
    }
    
    /**
     * Returns a new instance of a configured dependency.
     * 
     * @param string $name The name of the dependency.
     * @param array  $args The arguments to pass to the new instance.
     * 
     * @return mixed
     */
    public function createDependency($name, array $args = array())
    {
        return $this->resolve($name, $args)->create();
    }
    
    /**
     * Returns a configured instance of the specified dependency.
     * 
     * @param string $name The name of the dependency.
     * @param array  $args The arguments to pass if creating a new instance.
     * 
     * @return mixed
     */
    public function getDependency($name, array $args = array())
    {
        return $this->resolve($name, $args)->get();
    }
    
    /**
     * Map a dependency name to a class.
     * 
     * @param mixed  $map   An array of $map => $value or a dependency name for $class.
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
            $this->register($name, $class);
        }
        
        return $this;
    }
    
    /**
     * Detects the value of $value and handles it appropriately.
     *   - Instances of \Europa\Di\Dependency are registered on the container.
     *   - Other instances are created as a dependency then registered.
     * 
     * @param string $name  The name of the dependency.
     * @param mixed  $value One of many allowed values.
     * 
     * @throws \InvalidArgumentException If anything but a dependency object or other object instance is passed.
     * 
     * @return \Europa\Di\Container
     */
    public function register($name, $value)
    {
        if ($value instanceof Dependency) {
            $this->deps[$name] = $value;
        } elseif (is_object($value)) {
            $this->deps[$name] = new Dependency(get_class($value));
            $this->deps[$name]->set($value);
        } elseif (is_string($value)) {
            $this->map[$name] = $value;
        } else {
            throw new \InvalidArgumentException('Passed value must either be a dependency object, another object instance or a string class name of the class to map.');
        }
        
        return $this;
    }
    
    public function isRegistered($name)
    {
        return isset($this->deps[$name]);
    }
    
    public function unRegister($name)
    {
        if (!isset($this->deps[$name])) {
            unset($this->deps[$name]);
        }
        return $this;
    }
    
    /**
     * Sets a filter to use for converting a dependency name into a class name.
     * 
     * @param \Europa\Filter\FilterInterface $filter The filter to use for name formatting.
     * 
     * @return \Europa\Di\Container
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
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
        } elseif ($this->filter) {
            return $this->filter->filter($name);
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
