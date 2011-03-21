<?php

namespace Europa;

/**
 * A base class for views in Europa.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class View implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * The direct children of this view.
     * 
     * @var array
     */
    protected $children = array();
    
    /**
     * The parameters and helpers bound to the view.
     * 
     * @var array
     */
    protected $params = array();

    /**
     * The service container used for helpers.
     * 
     * @var \Europa\ServiceLocator
     */
    protected $serviceLocator;
       
    /**
     * Renders the view in whatever way necessary.
     * 
     * @return string
     */
    abstract public function __toString();
        
    /**
     * Attempts to call the specified method on the specified locator if it exists.
     * If none exists, then an undefined method exception is thrown.
     * 
     * @param string $name The specified service to locate and return.
     * @param array  $args The configuration for the service.
     * 
     * @return mixed
     */
    public function __call($name, array $args = array())
    {
        array_unshift($args, $this);
        if ($this->serviceLocator) {
            return $this->serviceLocator->create($name, $args);
        }
        throw new Exception('Call to undefined method "' . get_class($this) . '::' . $name . '()".');
    }
        
    /**
     * Attempts to retrieve a parameter by name. If the parameter is not found, then it attempts
     * to use the service locator to find a helper. If nothing is found, it returns null.
     * 
     * If a service locator is found, a new instance of the service is created and set as the
     * specified parameter. Subsequent invocations to __get will then return the same instance.
     * If you require a new instance every time, invoke __call.
     * 
     * @param string $name The name of the property to get or helper to load.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        } elseif ($this->serviceLocator) {
            $loc = $this->serviceLocator;
        } else {
            return null;
        }
        
        $this->params[$name] = $loc->create($name, array($this));
        return $this->params[$name];
    }
    
    /**
     * Sets a parameter.
     * 
     * @param string $name  The parameter to set.
     * @param mixed  $value The value to set.
     * 
     * @return bool
     */
    public function __set($name, $value)
    {
        $this->params[$name] = $value;
    }
    
    /**
     * Returns whether a parameter is set or not.
     * 
     * @param string $name The parameter to check.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->params);
    }
    
    /**
     * Unsets a parameter
     * 
     * @param string $name The parameter to unset.
     * 
     * @return void
     */
    public function __unset($name)
    {
        unset($this->params[$name]);
    }
    
    /**
     * Sets a child view. Setting children allows views to gather information about child views.
     * This is helpful, for example, if you want to load all css or js for the current view
     * and all of its children.
     * 
     * @return \Europa\View
     */
    public function setChild($name, \Europa\View $view)
    {
        $this->children[$name] = $view;
        return $this;
    }
    
    /**
     * Returns the specified child. If the child does not exist, an exception is thrown.
     * 
     * @throws \Europa\View\Exception If no child exists.
     * 
     * @return \Europa\View
     */
    public function getChild($name)
    {
        if (!isset($this->children[$name])) {
            throw new View\Exception('The child "' . $name . '" does not exist for view "' . get_class($this) . ' (' . $this->getScript() . '").');
        }
        return $this->children[$name];
    }
    
    /**
     * Returns all of the children belonging to this view.
     * 
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }
    
    /**
     * Returns a flat array containing all of the descendants for this view. Since the array
     * is flattened and the child names may conflict, they are formatted to represent their
     * ancestry. For example: "parent:child:grandchild".
     * 
     * @return array
     */
    public function getDescendants($separator = ':')
    {
        $descendants = array();
        foreach ($this->getChildren() as $name => $child) {
            $descendants[$name] = $child;
            foreach ($child->getDescendants() as $grandChildName => $grandChild) {
                $descendants[$name . $separator . $grandChildName] = $grandChild;
            }
        }
        return $descendants;
    }

    /**
     * Sets the service locator to use for calling helpers.
     * 
     * @param \Europa\ServiceLocator $serviceLocator The service locator to use for helpers.
     * 
     * @return \Europa\View
     */
    public function setServiceLocator(ServiceLocator $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }
    
    /**
     * Applies a group of parameters to the view.
     * 
     * @param mixed $params The params to set. Can be any iterable value.
     * 
     * @return Europa\View
     */
    public function setParams($params)
    {
        if (is_array($params) || is_object($params)) {
            foreach ($params as $name => $value) {
                $this->$name = $value;
            }
        }
        return $this;
    }
    
    /**
     * Returns the parameters bound to the view.
     * 
     * In most cases, this is will only be used when determining which
     * properties are public internally or when serializing view objects
     * externally.
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Returns whether or not the passed parameters exist.
     * 
     * @param array $params The parameters to check for.
     * 
     * @return bool
     */
    public function hasParams(array $params)
    {
        foreach ($params as $name) {
            if (!$this->__isset($name)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Removes all parameters.
     * 
     * @return Europa\View
     */
    public function removeParams()
    {
        $this->params = array();
        return $this;
    }
    
    /**
     * Sets the specified parameter.
     * 
     * @param string $name  The parameter name.
     * @param mixed  $value The parameter value.
     * 
     * @return Europa\View
     */
    public function offsetSet($name, $value)
    {
        $this->__set($name, $value);
        return $this;
    }
    
    /**
     * Returns the specified parameter.
     * 
     * @param string $name The parameter name.
     * 
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->__get($name);
    }
    
    /**
     * Returns whether or not the specified parameter exists.
     * 
     * @param string $name The parameter name.
     * 
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->__isset($name);
    }
    
    /**
     * Unsets the specified parameter.
     * 
     * @param string $name The parameter name.
     * 
     * @return Europa\View
     */
    public function offsetUnset($name)
    {
        $this->__unset($name);
        return $this;
    }
    
    /**
     * Returns the current parameter.
     * 
     * @return mixed
     */
    public function current()
    {
        return current($this->params);
    }
    
    /**
     * Returns the current parameter name.
     * 
     * @return string
     */
    public function key()
    {
        return key($this->params);
    }
    
    /**
     * Moves to the next parameter.
     * 
     * @return Europa\View
     */
    public function next()
    {
        next($this->params);
        return $this;
    }
    
    /**
     * Resets iteration.
     * 
     * @return Europa\View
     */
    public function rewind()
    {
        reset($this->params);
        return $this;
    }
    
    /**
     * Returns whether or not iteration is still valid.
     * 
     * @return bool
     */
    public function valid()
    {
        return isset($this->{$this->key()});
    }
    
    /**
     * Returns the number of parameters.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->params);
    }
}