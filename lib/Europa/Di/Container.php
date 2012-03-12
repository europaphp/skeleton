<?php

namespace Europa\Di;
use Europa\Filter\FilterInterface;
use Europa\Filter\UpperCamelCaseFilter;
use RuntimeException;

/**
 * The service injection container represents a collection of configured dependencies. Dependencies are instances
 * of \Europa\Di\Service that represent an object instance. The container provides a fluent interface for
 * accessing dependencies so that they can easily be configured.
 * 
 * @category ServiceInjection
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
     * Cached service instances.
     * 
     * @var array
     */
    private $deps = array();
    
    /**
     * Filter used for name formatting.
     * 
     * @var \Europa\Filter\FilterInterface
     */
    private $filters = array();
    
    /**
     * Container instances for static retrieval.
     * 
     * @var array
     */
    private static $containers = array();
    
    /**
     * The default instance name to use.
     * 
     * @var string
     */
    private static $defaultName = self::DEFAULT_INSTANCE_NAME;
    
    /**
     * Magic caller for resolve($name, $args).
     * 
     * @param string $name  The name of the service.
     * @param mixed  $value The service to register.
     * 
     * @see \Europa\Di\Service::register()
     */
    public function __call($name, array $args = array())
    {
        return $this->resolve($name)->configure($args);
    }
    
    /**
     * Magic caller for register().
     * 
     * @see \Europa\Di\Service::register()
     */
    public function __set($name, $value)
    {
        return $this->register($name, $value);
    }
    
    /**
     * Magic caller for resolve($name).
     * 
     * @see \Europa\Di\Service::resolve()
     */
    public function __get($name)
    {
        return $this->resolve($name);
    }
    
    /**
     * Magic caller for isRegistered($name).
     * 
     * @see \Europa\Di\Service::isRegistered()
     */
    public function __isset($name)
    {
        return $this->isRegistered($name);
    }
    
    /**
     * Magic caller for unregister($name).
     * 
     * @see \Europa\Di\Service::unregister()
     */
    public function __unset($name)
    {
        return $this->unregister($name);
    }
    
    /**
     * Creates a service if it doesn't already exist and returns it.
     * 
     * @param string $name The name of the service.
     * 
     * @return \Europa\Di\Service
     */
    public function resolve($name)
    {
        if (!isset($this->deps[$name])) {
            $dep = $this->resolveClassFromName($name);
            $dep = new Service($dep);
            $this->deps[$name] = $dep;
        }
        return $this->deps[$name];
    }
    
    /**
     * Returns a new instance of a configured service.
     * 
     * @param string $name The name of the service.
     * 
     * @return mixed
     */
    public function createService($name)
    {
        return $this->resolve($name)->create();
    }
    
    /**
     * Returns a configured instance of the specified service.
     * 
     * @param string $name The name of the service.
     * 
     * @return mixed
     */
    public function getService($name)
    {
        return $this->resolve($name)->get();
    }
    
    /**
     * Detects the value of $value and handles it appropriately.
     *   - Instances of \Europa\Di\Service are registered on the container.
     *   - Other instances are created as a service then registered.
     * 
     * @param string                      $name    The name of the service.
     * @param \Europa\Di\Service $service One of many allowed values.
     * 
     * @return Container
     */
    public function register($name, Service $service)
    {
        $this->deps[$name] = $service;
        return $this;
    }
    
    /**
     * Returns whether or not the specified service is registered.
     * 
     * @param string $name The service name.
     * 
     * @return bool
     */
    public function isRegistered($name)
    {
        return isset($this->deps[$name]);
    }
    
    /**
     * Removes the specified service.
     * 
     * @param string $name The service name.
     * 
     * @return Container
     */
    public function unRegister($name)
    {
        if (!isset($this->deps[$name])) {
            unset($this->deps[$name]);
        }
        return $this;
    }
    
    /**
     * Sets a filter to use for converting a service name into a class name.
     * 
     * @param \Europa\Filter\FilterInterface $filter The filter to use for name formatting.
     * 
     * @return Container
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }
    
    /**
     * Returns the class name for the specified service.
     * 
     * @param string $name The name of the service to get the class name for.
     * 
     * @return string
     */
    private function resolveClassFromName($name)
    {
        foreach ($this->filters as $filter) {
            $class = $filter->filter($name);
            if (class_exists($class, true)) {
                return $class;
            }
        }
        throw new \RuntimeException('The class name for the service "' . $name . '" could not be resolved.');
    }
    
    /**
     * Registers the container as an instance.
     * 
     * @param string    $name      The instance name.
     * @param Container $container The container to register.
     * 
     * @return void
     */
    public static function set($name, Container $container)
    {
        self::$containers[$name] = $container;
    }
    
    /**
     * Returns an instance of a container.
     * 
     * @param string $name The instance name to get if using multiple instances.
     * 
     * @return Container
     */
    public static function get($name = null)
    {
        $name = $name ? $name : self::$defaultName;
        if (!self::has($name)) {
            self::$containers[$name] = new static;
        }
        return self::$containers[$name];
    }
    
    /**
     * Returns whether or not a container by the specified name exists.
     * 
     * @param string $name The container name.
     * 
     * @return bool
     */
    public static function has($name)
    {
        return isset(self::$containers[$name]);
    }
    
    /**
     * Removes the container of the specified name.
     * 
     * @param string $name The container name.
     * 
     * @return void
     */
    public static function remove($name)
    {
        if (!self::has($name)) {
            throw new RuntimeException('The container "' . $name . '" does not exist.');
        }
        unset(self::$containers[$name]);
    }
    
    /**
     * Renames the specified container.
     * 
     * @param string $from The old name.
     * @param string $to   The new name.
     * 
     * @return void
     */
    public static function rename($from, $to)
    {
        if (!self::has($from)) {
            throw new RuntimeException('Cannot move container "' . $from . '" to "' . $to . '" because "' . $from . '" does not exist.');
        }
        
        if (self::has($to)) {
            throw new RuntimeException('Cannot move container "' . $from . '" to "' . $to . '" because "' . $to . '" already exists.');
        }
        
        self::set($to, self::get($from));
        self::remove($from);
    }
    
    /**
     * Sets the default instance name to use.
     * 
     * @param string $name The name to use.
     * 
     * @return void
     */
    public static function setDefaultName($name)
    {
        self::$defaultName = $name;
    }
}
