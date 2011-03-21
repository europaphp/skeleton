<?php

namespace Europa;
use Europa\ServiceLocator\Exception;

/**
 * A highly configurable dependency injection container.
 * 
 * @category DependencyInjection
 * @package  Europa
 * @author   Trey Shugart
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class ServiceLocator
{
    /**
     * Contains a service name to class name map.
     * 
     * @var
     */
    private $map = array();
    
    /**
     * Contains the configuration for the services.
     * 
     * @var array
     */
    private $config = array();
    
    /**
     * A method queue for the services.
     * 
     * @var array
     */
    private $queue = array();
    
    /**
     * Contains the shared service instances.
     * 
     * @var array
     */
    private $services = array();
    
    /**
     * The formatter for class names to use if there is no map.
     * 
     * @var mixed
     */
    private $formatter = array();
    
    /**
     * Holds all instantiated instances.
     * 
     * @var array
     */
    protected static $instances = array();
    
    /**
     * Instantiates the container setting the config for all services.
     * 
     * @param array $config The configuration for all of the services.
     * 
     * @return \Europa\ServiceLocator
     */
    public function __construct(array $config = array())
    {
        $this->setConfig($config);
    }
    
    /**
     * Calls the specified service using create().
     * 
     * @param string $name The name of the service to call.
     * @param array  $args The configuration to pass. The first element must be an array if specified.
     * 
     * @return mixed
     */
    public function __call($name, array $args = array())
    {
        $config = isset($args[0]) ? $args[0] : array();
        if (!is_array($config)) {
            throw new Exception('The parameter passed to service "' . $name . '" must be an array.');
        }
        return $this->get($name, $config);
    }
    
    /**
     * Returns the specified service using get().
     * 
     * @param string $name The service to get.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }
    
    /**
     * Maps a name to a class.
     * 
     * @param string $service The name of the service.
     * @param string $class   The name of the class it maps to.
     * 
     * @return \Europa\ServiceLocator
     */
    public function map($service, $class)
    {
        $this->map[$service] = $class;
        return $this;
    }
    
    /**
     * Sets a dependency instance to a service name.
     * 
     * @
     */
    public function set($service, $instance)
    {
        if (!is_object($instance)) {
            throw new Exception('The service "' . $service . '" must be a valid object instance.');
        }
        $this->services[$service] = $instance;
        return $this;
    }
    
    /**
     * Returns the specified service. If the service instance doesn't exist
     * yet then it is created, cached and returned.
     * 
     * @param string $service The name of the service.
     * @param array  $config  Configuration to use just for this instance.
     * 
     * @return mixed
     */
    public function get($service, array $config = array())
    {
        // only refresh the service if being reconfigured or doesn't exist yet
        if (!isset($this->services[$service]) || $config) {
            $this->services[$service] = $this->create($service, $config);
        }
        return $this->services[$service];
    }
    
    /**
     * Returns a new instance of the specified service.
     * 
     * @param string $service The name of the service.
     * @param array  $config  Any custom config just for this instance.
     * 
     * @return mixed
     */
    public function create($service, array $config = array())
    {
        // get the mapped or formatted name and it's config
        $class   = $service;
        $current = isset($this->config[$service]) ? $this->config[$service] : array();
        $config  = array_replace_recursive($current, $config);
        
        // the service may be a method protected or private and exist on the current object
        if (method_exists($this, $service)) {
            return call_user_func_array(array($this, $service), $config);
        }
        
        // if there is a class formatter, format the service name
        if ($this->formatter) {
            $class = call_user_func($this->formatter, $service);
        }
        
        // if a constructor exists, pass named arguments to it
        try {
            $class = new \ReflectionClass(isset($this->map[$service]) ? $this->map[$service] : $class);
        } catch (\Exception $e) {
            throw new Exception('Could not locate the service "' . $service . '".');
        }
        
        // pass configuration if __construct exists
        if (method_exists($class, '__construct')) {
            $class = $class->newInstanceArgs($config);
        } else {
            $class = $class->newInstance();
        }
        
        // go through the method queue and call them using named arguments
        if (isset($this->queue[$service])) {
            foreach ($this->queue[$service] as $method => $args) {
                call_user_func_array(array($class, $method), $args);
            }
        }
        
        return $class;
    }
    
    /**
     * Forces a refresh by removing the cached instance.
     * 
     * @param string $service The service to refresh.
     * 
     * @return \Europa\ServiceLocator
     */
    public function refresh($service)
    {
        if (isset($this->services[$service])) {
            unset($this->services[$service]);
        }
        return $this;
    }
    
    /**
     * Sets global configuration.
     * 
     * @param array $config The global config to set.
     * 
     * @return \Europa\ServiceLocator
     */
    public function setConfig(array $configs)
    {
        foreach ($configs as $name => $config) {
            $this->setConfigFor($name, $config);
        }
        return $this;
    }
    
    /**
     * Sets the configuration for the specified service.
     * 
     * @param string $service The name of the service to set the configuration for.
     * @param array  $config  The configuration for the service.
     * 
     * @return \Europa\ServiceLocator
     */
    public function setConfigFor($service, array $config)
    {
        // if setting the config for a service, we must make sure that it's refreshed
        $this->refresh($service);
        
        // reset the instance if there is one
        if (isset($this->services[$service])) {
            unset($this->services[$service]);
        }
        
        // get the current config if it exists, or default to empty
        $current = isset($this->config[$service]) ? $this->config[$service] : array();
        
        // merge config recursively
        $this->config[$service] = array_replace_recursive($current, $config);
        
        return $this;
    }
    
    /**
     * Queues up a method to be called when the specified service is created.
     * 
     * @param string $service The service to call the method on.
     * @param string $method  The method to call.
     * @param array  $args    The arguments to pass to the method.
     * 
     * @return \Europa\ServiceLocator
     */
    public function queueMethodFor($service, $method, array $args = array())
    {
        // if queueing another method, we must make sure the object is refresh when retrieved
        $this->refresh($service);
        
        // if the queue isn't set up, then initialize it
        if (!isset($this->queue[$service])) {
            $this->queue[$service] = array();
        }
        
        // and add it to the queue
        // the method name is the key and the arguments its value
        $this->queue[$service][$method] = $args;
        return $this;
    }
    
    /**
     * Sets the formatter to use for a service if no mapped class is found.
     * 
     * @param mixed $callback A callable callback to use for formatting service names to class names.
     * 
     * @return \Europa\ServiceLocator
     */
    public function setFormatter($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('The supplied callback is not callable.');
        }
        $this->formatter = $callback;
        return $this;
    }
    
    /**
     * Creates a new instance using the specified configuration and name. If no
     * name is specified, then a default instance is created and can facilitate
     * the singleton pattern.
     * 
     * @param mixed  $config The configuration array or the name of the instance. Defaults to 'default'.
     * @param string $name   The instance name if a config array is passed. Defaults to "default".
     * 
     * @return \Europa\ServiceLocator
     */
    public static function getInstance($config = array(), $name = 'default')
    {
        // allow a string as the first argument
        if (is_string($config)) {
            $name   = $config;
            $config = array();
        }
        
        // if an instance isn't defined yet, create one and statically cache it
        if (!isset(static::$instances[$name])) {
            static::$instances[$name] = new static($config);
        }
        
        return static::$instances[$name];
    }
}