<?php

abstract class Europa_ServiceContainer implements Iterator, ArrayAccess, Countable
{
    /**
     * Contains a service name to class name map.
     * 
     * @var
     */
    private $_map = array();
    
    /**
     * Contains the configuration for the services.
     * 
     * @var array
     */
    private $_config = array();
    
    /**
     * Contains the shared service instances.
     * 
     * @var array
     */
    private $_services = array();
    
    /**
     * Instantiates the container setting the config for all services.
     * 
     * @param array $config The configuration for all of the services.
     * 
     * @return Europa_ServiceContainer
     */
    public function __construct(array $config = array())
    {
        foreach ($config as $name => $value) {
            $this->offsetSet($name, $value);
        }
    }
    
    /**
     * Returns a new instance of the specifie service and passes it's configuration to
     * it's constructor. If any config is passed to the __call'ed method, then it is
     * merged with the default config.
     * 
     * @param string $service The service to return a new instance for.
     * @param array  $args    The configuration for the service to merge with the default.
     * 
     * @return mixed
     */
    public function __call($service, array $args = array())
    {
        if (!isset($args[0])) {
            $args[0] = array();
        }
        if (!is_array($args[0])) {
            throw new Europa_ServiceContainer_Exception(
                "The argument passed to the {$service} service constructor must be an array."
            );
        }
        $class  = $this->_getMappedClassFromName($service);
        $config = isset($this->_config[$service]) ? $this->_config[$service] : array();
        $config = $this->_merge($config, $args[0]);
        if (method_exists($this, $service)) {
            return $this->$service($config);
        }
        return new $class($config);
    }
    
    /**
     * Returns the specified service. If this service hasn't been instantiated yet,
     * it will be.
     * 
     * @param string $service The name of the service.
     * 
     * @return mixed
     */
    public function __get($service)
    {
        if (!isset($this->_services[$service])) {
            $this->_services[$service] = $this->__call($service);
        }
        return $this->_services[$service];
    }
    
    /**
     * Maps a service name to a dependency class.
     * 
     * @param string $class The class name of the dependency.
     * 
     * @return Europa_ServiceContainer
     */
    public function __set($name, $class)
    {
        $this->_map[$name] = $class;
        return $this;
    }
    
    /**
     * Returns whether or not the service has been shared.
     * 
     * @param string $service The name of the service.
     * 
     * @return bool
     */
    public function __isset($service)
    {
        return isset($this->_services[$service]);
    }
    
    /**
     * Unsets the shared instance of the specified service.
     * 
     * @param string $service The name of the servide.
     * 
     * @return mixed
     */
    public function __unset($service)
    {
        if (isset($this->_services[$service])) {
            unset($this->_services[$service]);
        }
        return $this;
    }
    
    /**
     * Returns the configuration for the service.
     * 
     * @param string $name The name of the service.
     * 
     * @return array
     */
    public function offsetGet($name)
    {
        // always an array
        if (!$this->offsetExists($name)) {
            $this->_config[$name] = array();
        }
        
        // return the config
        return $this->_config[$name];
    }
    
    /**
     * Sets the configuration for the service.
     * 
     * @param string $name   The name of the service.
     * @param mixed  $config The config for the service.
     * 
     * @return Europa_ServiceContainer
     */
    public function offsetSet($name, $value)
    {
        // make sure the value can be iterated over
        if (!is_array($value) && !is_object($value)) {
            throw new Europa_ServiceContainer_Exception(
                "The configuration for {$name} must be traversible."
            );
        }
        
        // set the values
        foreach ($value as $k => $v) {
            if (!isset($this->_config[$name]) || !is_array($this->_config[$name])) {
                $this->_config[$name] = array();
            }
            $this->_config[$name][$k] = $v;
        }
        
        // chain
        return $this;
    }
    
    /**
     * Checks to make sure the given service has configuration set. If it is set
     * to an empty array, then it returns false.
     * 
     * @param string $name The name of the service.
     * 
     * @return bool
     */
    public function offsetExists($name)
    {
        return isset($this->_config[$name]) && count($this->_config[$name]) > 0;
    }
    
    /**
     * Resets the configuration for the object. Same as setting it to an empty array.
     * 
     * @param string $name The name of the service.
     * 
     * @return Europa_ServiceContainer
     */
    public function offsetUnset($name)
    {
        // reset to an empty array
        $this->_config[$name] = array();
        return $this;
    }
    
    /**
     * Returns the current service in the collection.
     * 
     * @return mixed
     */
    public function current()
    {
        return current($this->_services);
    }
    
    /**
     * Returns the name of the current service in the collection.
     * 
     * @return string
     */
    public function key()
    {
        return key($this->_services);
    }
    
    /**
     * Moves to the next service in the collection.
     * 
     * @return Europa_ServiceContainer
     */
    public function next()
    {
        next($this->_services);
        return $this;
    }
    
    /**
     * Returns to the first service in the collection.
     * 
     * @return mixed
     */
    public function rewind()
    {
        reset($this->_services);
        return $this;
    }
    
    /**
     * Returns whether or not we can keep iterating through the service collection.
     * 
     * @return bool
     */
    public function valid()
    {
        return !is_null($this->current());
    }
    
    /**
     * Returns the number of services in the collection.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->_services);
    }
    
    /**
     * Returns the class name form the given service name.
     * 
     * @param string $service The service name.
     * 
     * @return string
     */
    private function _getMappedClassFromName($service)
    {
        $class = $service;
        if (isset($this->_map[$service])) {
            $class = $this->_map[$service];
        }
        return $class;
    }
    
    /**
     * Works with _recurse to be a replacement for array_repalce_recursive
     * for PHP versions less than 5.3.0.
     * 
     * @param array $array1 The first array.
     * @param array $array2 The second array.
     * 
     * @return array
     */
    private function _merge(array $array1, $array2)
    {
        $args  = func_get_args();
        $array = $args[0];
        if (!is_array($array)) {
            return $array;
        }
        for ($i = 1; $i < count($args); $i++) {
            if (is_array($args[$i])) {
                $array = $this->_recurse($array, $args[$i]);
            }
        }
        return $array;
    }
    
    /**
     * Used by _merge to be a replacement for array_repalce_recursive
     * for PHP versions less than 5.3.0.
     * 
     * @param array $array1 The first array.
     * @param array $array2 The second array.
     * 
     * @return array
     */
    private function _recurse($array1, $array2)
    {
        foreach ($array2 as $key => $value) {
            if (!isset($array1[$key]) || (isset($array1[$key]) && !is_array($array1[$key]))) {
                $array1[$key] = array();
            }
            if (is_array($value)) {
                $value = $this->_recurse($array1[$key], $value);
            }
            $array1[$key] = $value;
        }
        return $array1;
    }
}