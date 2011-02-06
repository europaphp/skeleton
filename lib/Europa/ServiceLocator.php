<?php

namespace Europa;

/**
 * An extensible dependency injection container.
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
    protected $map = array();
    
    /**
     * Contains the configuration for the services.
     * 
     * @var array
     */
    protected $config = array();
    
    /**
     * A method queue for the services.
     * 
     * @var array
     */
    protected $queue = array();
    
    /**
     * Contains the shared service instances.
     * 
     * @var array
     */
    protected $services = array();
    
    /**
     * The class formatter for the current instance.
     * 
     * @var mixed
     */
    protected $formatter;
    
    /**
     * The default formatter for all instances.
     * 
     * @var mixed
     */
    protected static $defaultFormatter;
    
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
        if (static::$defaultFormatter) {
            $this->setFormatter(static::$defaultFormatter);
        }
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
            throw new Exception('The parameter passed to "' . $name . '" must be an array.');
        }
        return $this->create($name, $config);
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
        if (!isset($this->services[$service])) {
            $this->refresh($service, $config);
        }
        return $this->services[$service];
    }
    
    /**
     * Re-configures a cached object.
     * 
     * @param string $service The object to refresh.
     * @param array  $config  The configuration for the object.
     * 
     * @return \Europa\ServiceContainer
     */
    public function refresh($service, array $config = array())
    {    
        $this->services[$service] = $this->create($service, $config);
        return $this;
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
        $class   = $this->getMappedClassFromName($service);
        $current = isset($this->config[$service]) ? $this->config[$service] : array();
        $config  = array_replace_recursive($current, $config);
        
        // the service may be a method protected or private and exist on the current object
        if (method_exists($this, $service)) {
            $method = new Reflection\MethodReflector($this, $service);
            return call_user_func_array(
                array($this, $service),
                $method->mergeNamedArgs($config)
            );
        }
        
        // or just default to using the passing the config to the class
        $classReflector = new \ReflectionClass($class);
        if (method_exists($class, '__construct')) {
            $method = new Reflection\MethodReflector($class, '__construct');
            $class  = $classReflector->newInstanceArgs($method->mergeNamedArgs($config));
        } else {
            $class = new $class;
        }
        
        // go through the method queue and call them
        if (isset($this->queue[$service])) {
            foreach ($this->queue[$service] as $method => $args) {
                $method = new Reflection\MethodReflector($class, $method);
                $method->invokeNamedArgs($class, $args);
            }
        }
        
        return $class;
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
        if (!isset($this->queue[$service])) {
            $this->queue[$service] = array();
        }
        $this->queue[$service][$method] = $args;
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
     * Returns the full configuration array.
     * 
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
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
     * Returns the configuration array for the specified service.
     * 
     * @param strign $service The service to return the configuration for.
     * 
     * @return array
     */
    public function getConfigFor($service)
    {
        if (!isset($this->config[$service])) {
            throw new ServiceLocator\Exception('The service "' . $service . '" is not configured yet.');
        }
        return $this->config[$service];
    }

    /**
     * Sets the specified formatter for the current instance.
     * 
     * @param mixed $formatter A callable formatter to use for class formatting.
     * 
     * @return \Europa\ServiceLocator
     */
    public function setFormatter($formatter)
    {
        if (!is_callable($formatter)) {
            throw new Exception('The passed formatter is not callable.');
        }
        $this->formatter = $formatter;
        return $this;
    }
    
    /**
     * Sets a default formatter for all instances.
     * 
     * @param mixed $formatter A callable formatter to use for class formatting.
     * 
     * @return void
     */
    public static function setDefaultFormatter($formatter)
    {
        if (!is_callable($formatter)) {
            throw new Exception('The passed formatter is not callable.');
        }
        static::$defaultFormatter = $formatter;
    }
    
    /**
     * Creates a new instance using the specified configuration and name. If no
     * name is specified, then a default instance is created and can fascilitate
     * the singleton pattern.
     * 
     * @param array  $config The configuration array.
     * @param string $name   The instance name. Defaults to "default".
     * 
     * @return \Europa\ServiceLocator
     */
    public static function getInstance(array $config = array(), $name = 'default')
    {
        if (!isset(static::$instances[$name])) {
            static::$instances[$name] = new static($config);
        }
        return static::$instances[$name];
    }
    
    /**
     * Returns the class name form the given service name.
     * 
     * @param string $service The service name.
     * 
     * @return string
     */
    protected function getMappedClassFromName($service)
    {
        $class = $service;
        if (isset($this->map[$service])) {
            $class = $this->map[$service];
        } elseif ($this->formatter) {
            $class = call_user_func($this->formatter, $service);
        }
        return $class;
    }
}