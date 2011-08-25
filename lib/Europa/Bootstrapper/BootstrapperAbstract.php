<?php

namespace Europa\Bootstrapper;

/**
 * When invoked by a child class, it goes through each method in the order which they were defined and executes it.
 * Options can also be used to add dynamics to the bootstrapping process.
 *
 * @category Application
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class BootstrapperAbstract implements BootstrapperInterface
{
    /**
     * The configuration options for the current instance. To set defaults, define a __construct method in the child
     * class and set the options from there.
     * 
     * @var array
     */
    private $options = array();
    
    /**
     * Iterates through each method in the extending class and calls them in the order in which they were defined.
     * 
     * @return void
     */
    final public function __invoke()
    {
        $class = new \ReflectionClass($this);
        foreach ($class->getMethods() as $method) {
            if ($this->isValidMethod($method)) {
                $method->invoke($this);
            }
        }
    }
    
    /**
     * Sets an option value.
     * 
     * @param string $name  The option name.
     * @param mixed  $value The option value.
     * 
     * @return \Europa\Bootstrapper\BootstrapperAbstract
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
     * @return \Europa\Bootstrapper\BootstrapperAbstract
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
     * @return \Europa\Bootstrapper\BootstrapperAbstract
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
     * @return \Europa\Bootstrapper\BootstrapperAbstract
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
     * @return \Europa\Bootstrapper\BootstrapperAbstract
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
     * @return \Europa\Bootstrapper\BootstrapperAbstract
     */
    final public function removeOption($name)
    {
        if ($this->hasOption($name)) {
            unset($this->options[$name]);
        }
        return $this;
    }
    
    /**
     * Sets multiple options at once. Passed options can be an object or an array.
     * 
     * @param mixed $options The options to set for this type of object.
     * 
     * @return \Europa\Bootstrapper\BootstrapperAbstract
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
     * Returns all options.
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
     * @return \Europa\Bootstrapper\BootstrapperAbstract
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
