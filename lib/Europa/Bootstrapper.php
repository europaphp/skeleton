<?php

namespace Europa;

/**
 * A class used for application setup. Defined methods are called in the order in which they are defined.
 *
 * @category Bootstrapping
 * @package  Bootstrapper
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class Bootstrapper
{
    /**
     * The default configuration options for all instances.
     * 
     * @var array
     */
    private $options = array();
    
    /**
     * Goes through each method in the extending class and calls them in the order in which they were defined.
     * 
     * @return Bootstrapper
     */
    final public function __invoke()
    {
        $class = new \ReflectionClass($this);
        foreach ($class->getMethods() as $method) {
            if (!$this->isValidMethod($method)) {
                continue;
            }
            $method->invoke($this);
        }
        return $this;
    }
    
    /**
     * Sets an option value.
     * 
     * @param string $name  The option name.
     * @param mixed  $value The option value.
     * 
     * @return Bootstrapper
     */
    final public function __set($name, $value)
    {
        return $this->setOption($name, $value);
    }
    
    /**
     * Returns an option value.
     * 
     * @param string $name The option name.
     * 
     * @return mixed
     */
    final public function __get($name)
    {
        return $this->getOption($name);
    }
    
    /**
     * Checks for the existence of an option.
     * 
     * @param string $name The option name.
     * 
     * @return Bootstrapper
     */
    final public function __isset($name)
    {
        return $this->hasOption($name);
    }
    
    /**
     * Removes an option.
     * 
     * @param string $name The option name.
     * 
     * @return Bootstrapper
     */
    final public function __unset($name)
    {
        return $this->removeOption($name);
    }
    
    /**
     * Sets an option value.
     * 
     * @param string $name  The option name.
     * @param mixed  $value The option value.
     * 
     * @return Bootstrapper
     */
    final public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }
    
    /**
     * Returns an option value.
     * 
     * @param string $name The option name.
     * 
     * @return mixed
     */
    final public function getOption($name)
    {
        if ($this->hasOption($name)) {
            return $this->options[$name];
        }
        return null;
    }
    
    /**
     * Checks for the existence of an option.
     * 
     * @param string $name The option name.
     * 
     * @return Bootstrapper
     */
    final public function hasOption($name)
    {
        return isset($this->options[$name]);
    }
    
    /**
     * Removes an option.
     * 
     * @param string $name The option name.
     * 
     * @return Bootstrapper
     */
    final public function removeOption($name)
    {
        if ($this->hasOption($name)) {
            unset($this->options[$name]);
        }
        return $this;
    }
    
    /**
     * Sets the default configuration for the bootstrapper. Uses late static binding so that different objects can have
     * separate default configurations.
     * 
     * @param array $options The options to set for this type of object.
     * 
     * @return Bootstrapper
     */
    final public function setOptions($options)
    {
        if (is_object($options) || is_array($options)) {
            foreach ($options as $name => $value) {
                $this->setOption($name, $value);
            }
        }
        return $this;
    }
    
    /**
     * Returns the default options.
     * 
     * @return array
     */
    final public function getOptions()
    {
        return $this->defaultOptions;
    }
    
    /**
     * Removes all options.
     * 
     * @return Bootstrapper
     */
    final public function removeOptions()
    {
        $this->options = array();
        return $this;
    }
    
    /**
     * Returns whether or not the method is a valid bootstrap method.
     * 
     * @param \ReflectionMethod $method The method to check.
     * 
     * @return bool
     */
    private function isValidMethod(\ReflectionMethod $method)
    {
        if ($method->isConstructor()) {
            return false;
        }
        
        if (!$method->isPublic()) {
            return false;
        }
        
        if ($method->class !== get_class($this)) {
            return false;
        }
        
        if (strpos($method->getName(), '__') === 0) {
            return false;
        }
        
        return true;
    }
}