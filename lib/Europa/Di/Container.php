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
     * Returns the specified dependency.
     * 
     * @param string $name The dependency name.
     * 
     * @return \Europa\Di\Dependency
     */
    public function __get($name)
    {
        if (!isset($this->deps[$name])) {
            $this->deps[$name] = $this->create($name);
        }
        return $this->deps[$name];
    }
    
    /**
     * Tells the container to configure the specified dependency when it is created.
     * 
     * @param string $name The dependency name.
     * @param array  $args The config.
     * 
     * @return \Europa\Di\Container
     */
    public function configure($name, array $args = array())
    {
        $this->config[$name] = $args;
        return $this;
    }
    
    /**
     * Tells the container to queue the specified method when it is created.
     * 
     * @param string $name   The dependency name.
     * @param string $method The method name.
     * @param array  $args   The method args.
     * 
     * @return \Europa\Di\Container
     */
    public function queue($name, $method, array $args = array())
    {
        $this->queue[$name] = array($method, $args);
        return $this;
    }
    
    /**
     * Maps the specified map to the specified class.
     * 
     * @param string $map   An array mapping or name of the dependency.
     * @param string $class The class to map the dependency to if the first argument is not an array.
     * 
     * @return \Europa\Di\Container
     */
    public function map($map, $class = null)
    {
        if (!is_array($map)) {
            $map = array($map, $class);
        }
        foreach ($map as $name => $class) {
            $this->map[$name] = $class;
        }
        return $this;
    }
    
    /**
     * Sets the formatter to format the dependency names into class names.
     * 
     * @param mixed $formatter A callable parameter to return a formatted dependency class from the name.
     * 
     * @return \Europa\Di\Container
     */
    public function setFormatter($formatter)
    {
        if (!is_callable($formatter)) {
            throw new Exception("The specified formatter must be callable.");
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
     * Creates a dependency instance from the name. Applies preset config and queue.
     * 
     * @param string $name The dependency name.
     * 
     * @return \Europa\Di\Dependency
     */
    private function create($name)
    {
        $dep = $this->getClassNameFor($name);
        $dep = new Dependency($dep);
        if (isset($this->config[$name])) {
            $dep->configure($this->config[$name]);
        }
        if (isset($this->queue[$name])) {
            foreach ($this->queue[$name] as $queue) {
                $dep->queue($queue[0], $queue[1]);
            }
        }
        return $dep;
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