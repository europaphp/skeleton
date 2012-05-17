<?php

namespace Europa\Di;
use Closure;
use Europa\Filter\FilterInterface;
use Europa\Filter\UpperCamelCaseFilter;
use RuntimeException;

/**
 * The service injection container represents a collection of configured dependencies. Dependencies are instances
 * of Service that represent an object instance. The container provides a fluent interface for
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
     * Global configurations for types of objects.
     * 
     * @var array
     */
    private $configs = [];
    
    /**
     * Cached service instances.
     * 
     * @var array
     */
    private $deps = [];
    
    /**
     * Filter used for class name formatting.
     * 
     * @var FilterInterface
     */
    private $filter;
    
    /**
     * Container instances for static retrieval.
     * 
     * @var array
     */
    private static $containers = [];
    
    /**
     * The default instance name to use.
     * 
     * @var string
     */
    private static $defaultName = self::DEFAULT_INSTANCE_NAME;
    
    /**
     * Returns a new instance for the specified service.
     * 
     * @param string $name The name of the service.
     * @param array  $args The arguments, if any, to configure the service with.
     * 
     * @return mixed
     */
    public function __call($name, array $args = [])
    {
        return $this->resolve($name)->create($args);
    }
    
    /**
     * Returns an existing instance for the specified service.
     * 
     * @param string $name The name of the service.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        return $this->resolve($name)->get();
    }
    
    /**
     * Returns whether or not the specified service is registered.
     * 
     * @param string $name The service name.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return $this->resolve($name)->exists();
    }
    
    /**
     * Removes the specified service.
     * 
     * @param string $name The service name.
     * 
     * @return Container
     */
    public function __unset($name)
    {
        if (!isset($this->deps[$name])) {
            unset($this->deps[$name]);
        }
        
        return $this;
    }
    
    /**
     * Sets the global configuration for a particular type.
     * 
     * @param string  $type   The type of object to configure.
     * @param Closure $config The configuration closure to configure the type of service.
     * 
     * @return Container
     */
    public function config($type, Closure $config)
    {
        $this->configs[$type] = $config;
        return $this;
    }
    
    /**
     * Creates a service if it doesn't already exist and returns it.
     * 
     * @param string $name The name of the service.
     * 
     * @return Service
     */
    public function resolve($name)
    {
        if (!isset($this->deps[$name])) {
            $dep = $this->resolveClassFromName($name);
            $dep = new Service($dep);
            
            // search for a global configuration for an object of this type
            foreach ($this->configs as $type => $config) {
                if ($dep->is($type)) {
                    $dep->config($config);
                }
            }
            
            $this->deps[$name] = $dep;
        }
        
        return $this->deps[$name];
    }
    
    /**
     * Detects the value of $value and handles it appropriately.
     *   - Instances of Service are registered on the container.
     *   - Other instances are created as a service then registered.
     * 
     * @param string  $name    The name of the service.
     * @param Service $service One of many allowed values.
     * 
     * @return Container
     */
    public function register($name, Service $service)
    {
        $this->deps[$name] = $service;
        
        return $this;
    }
    
    /**
     * If the specified service is not regiseterd an exception is raised using the specified error or a default error.
     * 
     * @param string $name  The service to expect.
     * @param string $type  The type of object to expect.
     * @param string $error The error message to use.
     * 
     * @return Container
     */
    public function expect($name, $type, $error = null)
    {
        // default error message
        if (!$error) {
            $error = 'The service "' . $name . '" is required and must be an instance of "' . $type . '."';
        }
        
        // throw error
        if (!isset($this->$name) || ($type && !$this->resolve($name)->is($type))) {
            throw new RuntimeException($error);
        }
        
        return $this;
    }
    
    /**
     * Sets a filter to use for converting a service name into a class name.
     * 
     * @param FilterInterface $filter The filter to use for name formatting.
     * 
     * @return Container
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
        
        return $this;
    }
    
    /**
     * Returns the filter used by the container to resolve classes.
     * 
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
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
        if ($this->filter) {
            $name = $this->filter->filter($name);
        }
        
        return $name;
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
        
        if (!self::exists($name)) {
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
    public static function exists($name)
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
        if (!self::exists($name)) {
            throw new RuntimeException('The container "' . $name . '" does not exist.');
        }
        
        unset(self::$containers[$name]);
    }
    
    /**
     * Renames the specified container.
     * 
     * @param string $from      The old name.
     * @param string $to        The new name.
     * @param bool   $overwrite Whether or not to allow overwriting.
     * 
     * @return void
     */
    public static function rename($from, $to, $overwrite = false)
    {
        if (!self::exists($from)) {
            throw new RuntimeException(
                'Cannot move container "'
                . $from
                . '" to "'
                . $to
                . '" because "'
                . $from
                . '" does not exist.'
            );
        }
        
        if ($overwrite && self::exists($to)) {
            throw new RuntimeException(
                'Cannot move container "'
                . $from
                . '" to "'
                . $to
                . '" because "'
                . $to
                . '" already exists.'
            );
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
