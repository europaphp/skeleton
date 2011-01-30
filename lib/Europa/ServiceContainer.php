<?php

namespace Europa;

/**
 * An extensible dependency injection container.
 * 
 * @category DependencyInjection
 * @package  Europa
 * @author   Trey Shugart
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class ServiceContainer
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
     * Contains the shared service instances.
     * 
     * @var array
     */
    protected $services = array();
    
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
     * @return \Europa\ServiceContainer
     */
    public function __construct(array $config = array())
    {
        $this->setConfig($config);
    }
    
    /**
     * Maps a name to a class.
     * 
     * @param string $service The name of the service.
     * @param string $class   The name of the class it maps to.
     * 
     * @return \Europa\ServiceContainer
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
     * 
     * @return mixed
     */
    public function get($service)
    {
        if (!isset($this->services[$service])) {
            $this->services[$service] = $this->getNew($service);
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
    public function getNew($service, array $config = array())
    {
        // get the class name and it's config
        $class   = $this->getMappedClassFromName($service);
        $current = isset($this->config[$service]) ? $this->config[$service] : array();
        $config  = array_replace_recursive($current, $config);
        
        // the service may be a method protected or private and exist on 
        // the current object
        if (method_exists($this, $service)) {
            $method = new Reflection\Method($this, $service);
            return call_user_func_array(
                array($this, $service),
                $method->mergeNamedArgs($config)
            );
        }
        
        // or just default to using the passing the config to the class
        if (method_exists($class, '__construct')) {
            $method = new Reflection\Method($class, '__construct');
            $class  = new \ReflectionClass($class);
            return $class->invokeArgs($method->mergeNamedArgs($config));
        }
        
        // if no constructor present, just return a new instance
        return new $class;
    }
    
    /**
     * Sets global configuration.
     * 
     * @param array $config The global config to set.
     * 
     * @return \Europa\ServiceContainer
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
     * @return \Europa\ServiceContainer
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
            throw new ServiceContainer\Exception('The service "' . $service . '" is not configured yet.');
        }
        return $this->config[$service];
    }
    
    /**
     * Creates a new instance using the specified configuration and name. If no
     * name is specified, then a default instance is created and can fascilitate
     * the singleton pattern.
     * 
     * @param array  $config The configuration array.
     * @param string $name   The instance name. Defaults to "default".
     * 
     * @return \Europa\ServiceContainer
     */
    public static function getInstance(array $config = array(), $name = 'default')
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new static($config);
        }
        return self::$instances[$name];
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
        }
        return $class;
    }
}