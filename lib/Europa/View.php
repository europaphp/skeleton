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
abstract class View
{
    /**
     * The parameters and helpers bound to the view.
     * 
     * @var array
     */
    private $params = array();
    
    /**
     * The children of the current view.
     * 
     * @var array
     */
    private $children = array();
    
    /**
     * The parent associated to the current view.
     * 
     * @var \Europa\View|null
     */
    private $parent;
    
    /**
     * Whether or not to cascade parameters.
     * 
     * @var bool
     */
    private $cascadeParams = true;
    
    /**
     * Renders the view in whatever way necessary.
     * 
     * @return string
     */
    abstract public function render();
    
    /**
     * Sets the specified parameter.
     * 
     * @param string $name The parameter to set.
     * 
     * @return void
     */
    public function __set($name, $value)
    {
        return $this->setParam($name, $value);
    }
    
    /**
     * Returns the value of the specified parameter. If it is not found, then it returns null.
     * 
     * @param string $name The parameter to get.
     * 
     * @return void
     */
    public function __get($name)
    {
        return $this->getParam($name);
    }
    
    /**
     * Checks to see if the specified parameter is set.
     * 
     * @param string $name The parameter to check for.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return $this->hasParam($name);
    }
    
    /**
     * Unsets the specified parameter.
     * 
     * @param string $name The parameter to unset.
     * 
     * @return void
     */
    public function __unset($name)
    {
        return $this->removeParam($name);
    }
    
    /**
     * Tells the view whether or not to allow parameter cascading.
     * 
     * @param bool $switch Whether or not to cascade parameters.
     * 
     * @return \Europa\View
     */
    public function cascadeParams($switch = true)
    {
        $this->cascadeParams = $switch ? true : false;
        return $this;
    }
    
    /**
     * Sets a parameter on the view. Additionally, the parameter is set on all child views if parameter cascading is
     * enabled.
     * 
     * @param string $name  The parameter name.
     * @param mixed  $value The parameter value.
     * 
     * @return \Europa\View
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        if ($this->cascadeParams) {
            foreach ($this->children as $child) {
                $child->setParam($name, $value);
            }
        }
        return $this;
    }
    
    /**
     * Gets a parameter from the view.
     * 
     * @param string $name The parameter name.
     * 
     * @return mixed
     */
    public function getParam($name)
    {
        if ($this->__isset($name)) {
            return $this->params[$name];
        }
        return null;
    }
    
    /**
     * Returns whether or not the specified parameter exists.
     * 
     * @param string $name The parameter name.
     * 
     * @return bool
     */
    public function hasParam($name)
    {
        return array_key_exists($name, $this->params);
    }
    
    /**
     * Removes the specified parameter.
     * 
     * @param string $name The parameter name.
     * 
     * @return \Europa\View
     */
    public function removeParam($name)
    {
        if ($this->hasParam($name)) {
            unset($this->params[$name]);
        }
        return $this;
    }
    
    /**
     * Applies a group of parameters to the view.
     * 
     * @param mixed $params The params to set.
     * 
     * @return \Europa\View
     */
    public function setParams($params)
    {
        if (is_array($params) || is_object($params)) {
            foreach ($params as $name => $value) {
                $this->setParam($name, $value);
            }
        }
        return $this;
    }
    
    /**
     * Returns the parameters bound to the view.
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
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
}