<?php

namespace Europa;
use Europa\View\Exception;

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
    public function setParams(array $params)
    {
        foreach ($params as $name => $value) {
            $this->setParam($name, $value);
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
    
    /**
     * Adds a child to the current view by name.
     * 
     * @param string       $name The name of the child.
     * @param \Europa\View $view The child to set.
     * 
     * @return \Europa\View
     */
    public function setChild($name, View $view)
    {
        $this->children[$name] = $view;
        $view->setParent($this);
        return $this;
    }
    
    /**
     * Returns the specified child instance by its name. If the child does not exist an exception is thrown.
     * 
     * @param string $name The name of the child to get.
     * 
     * @return \Europa\View
     */
    public function getChild($name)
    {
        if (!isset($this->children[$name])) {
            throw new Exception('The child "' . $name . '" does not exist for "' . $this->getScript() . '"');
        }
        return $this->children[$name];
    }
    
    /**
     * Removes the specified child from the current view. The specified child can be either an instance of the child
     * that you want to remove or a string representing the name of the child that was specified when it was set.
     * 
     * @param \Europa\View|string $childToRemove The instance of name of the child to remove.
     * 
     * @return \Europa\View
     */
    public function removeChild($childToRemove)
    {
        foreach ($this->children as $name => $child) {
            if ($childToRemove instanceof View) {
                $matched = $childToRemove === $child;
            } else {
                $matched = $childToRemove === $name;
            }
            
            if ($matched) {
                unset($this->children[$name]);
                break;
            }
        }
        return $this;
    }
    
    /**
     * Returns all of the descendants of the current view as a flat array.
     * 
     * @return array
     */
    public function getDescendants()
    {
        $descendants = array();
        foreach ($this->children as $name => $child) {
            $descendants[$name] = $child;
            foreach ($child->getDescendants() as $descName => $descChild) {
                $descendatns[$name] = $desc;
            }
        }
        return $descendants;
    }
    
    /**
     * Sets the immediate parent of the current view. If the child has a current parent, it is removed from that parent
     * as views can only have a single parent.
     * 
     * @param \Europa\View $view The parent.
     * 
     * @return \Europa\View
     */
    public function setParent(View $view)
    {
        $this->parent = $view;
        return $this;
    }
    
    /**
     * Returns the immediate parent of the current view if it exists.
     * 
     * @return \Europa\View|null
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /** 
     * Detaches the parent of the current view from the current view.
     * 
     * @return \Europa\View
     */
    public function removeParent()
    {
        $this->parent->removeChild($this);
        return $this;
    }
    
    /**
     * Returns the ancestors of the current view as a flat array. The first parent being the first and the top-most
     * being the last.
     * 
     * @return array
     */
    public function getAncestors()
    {
        $parent    = $this->getParent();
        $ancestors = array();
        while ($parent->getParent() instanceof View) {
            $ancestors[] = $parent->getParent();
        }
        return $ancestors;
    }
}