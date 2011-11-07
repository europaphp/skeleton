<?php

namespace Europa\Application;
use Europa\Filter\FilterInterface;
use Europa\Filter\UpperCamelCaseFilter;

/**
 * The dependency injection container represents a collection of configured dependencies. Dependencies are instances
 * of \Europa\Application\Dependency that represent an object instance. The container provides a fluent interface for
 * accessing dependencies so that they can easily be configured.
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
     * Container instances for static retrieval.
     * 
     * @var array
     */
    private static $containers = array();
    
    /**
     * Sets up the container.
     * 
     * @return \Europa\Application\Container
     */
    public function __construct()
    {
        $this->setFilter(new UpperCamelCaseFilter);
    }
    
    /**
     * Magic caller for resolve($name, $args).
     * 
     * @param string $name  The name of the dependency.
     * @param mixed  $value The dependency to register.
     * 
     * @see \Europa\Application\Dependency::register()
     */
    public function __call($name, array $args = array())
    {
        return $this->resolve($name)->configure($args);
    }
    
    /**
     * Magic caller for register().
     * 
     * @see \Europa\Application\Dependency::register()
     */
    public function __set($name, $value)
    {
        return $this->register($name, $value);
    }
    
    /**
     * Magic caller for resolve($name).
     * 
     * @see \Europa\Application\Dependency::resolve()
     */
    public function __get($name)
    {
        return $this->resolve($name);
    }
    
    /**
     * Magic caller for isRegistered($name).
     * 
     * @see \Europa\Application\Dependency::isRegistered()
     */
    public function __isset($name)
    {
        return $this->isRegistered($name);
    }
    
    /**
     * Magic caller for unregister($name).
     * 
     * @see \Europa\Application\Dependency::unregister()
     */
    public function __unset($naem)
    {
        return $this->unregister($name);
    }
    
    /**
     * Creates a dependency if it doesn't already exist and returns it.
     * 
     * @param string $name The name of the dependency.
     * 
     * @return \Europa\Application\Dependency
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
        return $this->resolve($name)->configure($args)->create();
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
        return $this->resolve($name)->configure($args)->get();
    }
    
    /**
     * Detects the value of $value and handles it appropriately.
     *   - Instances of \Europa\Application\Dependency are registered on the container.
     *   - Other instances are created as a dependency then registered.
     * 
     * @param string      $name       The name of the dependency.
     * @param Dependency  $dependency One of many allowed values.
     * 
     * @return \Europa\Application\Container
     */
    public function register($name, Dependency $dependency)
    {
        $this->deps[$name] = $dependency;
        return $this;
    }
    
    /**
     * Returns whether or not the specified dependency is registered.
     * 
     * @param string $name The dependency name.
     * 
     * @return bool
     */
    public function isRegistered($name)
    {
        return isset($this->deps[$name]);
    }
    
    /**
     * Removes the specified dependency.
     * 
     * @param string $name The dependency name.
     * 
     * @return \Europa\Application\Container
     */
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
     * @return \Europa\Application\Container
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
        return $this;
    }
    
    /**
     * Returns the class name for the specified dependency.
     * 
     * @param string $name The name of the dependency to get the class name for.
     * 
     * @return string
     */
    private function getClassNameFor($name)
    {
        return $this->filter->filter($name);
    }
    
    /**
     * Returns an instance of a container.
     * 
     * @param string $name The instance name to get if using multiple instances.
     * 
     * @return \Europa\Application\Container
     */
    public static function get($name = self::DEFAULT_INSTANCE_NAME)
    {
        if (!isset(static::$containers[$name])) {
            static::$containers[$name] = new static;
        }
        return static::$containers[$name];
    }
}
