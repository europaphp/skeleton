<?php

namespace Europa;

/**
 * The heart of EuropaPHP. This is where it all starts and ends.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class Request implements \Iterator, \ArrayAccess, \Countable
{
    /**
     * The key used to get the controller from the request params.
     * 
     * @var string
     */
    private $controllerKey = 'controller';
    
    /**
     * The callback to use for formatting the controller parameter.
     * 
     * @var mixed
     */
    private $controllerFormatter = null;
    
    /**
     * The params parsed out of the route and cascaded through the
     * super-globals. Contains the default controller to use.
     * 
     * @var array
     */
    private $params = array('controller' => 'index');
    
    /**
     * Automates dispatching and converting the controller to a string.
     * 
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->dispatch()->__toString();
        } catch (\Exception $e) {
            $e = new Exception($e->getMessage(), $e->getCode());
            $e->trigger();
        }
    }
    
    /**
     * Returns the specified request parameter.
     * 
     * @param string $name The name of the parameter.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        if ($this->__isset($name)) {
            return $this->params[$name];
        }
        return null;
    }
    
    /**
     * Sets the specified request parameter.
     * 
     * @param string $name  The name of the parameter.
     * @param mixed  $value The value of the parameter.
     * 
     * @return mixed
     */
    public function __set($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }
    
    /**
     * Checks for the specified parameter.
     * 
     * @param string $name The parameter to check for.
     * 
     * @return \Europa\Request
     */
    public function __isset($name)
    {
        return isset($this->params[$name]);
    }
    
    /**
     * Unsets the specified parameter.
     * 
     * @param string $name The parameter to unset.
     * 
     * @return \Europa\Request
     */
    public function __unset($name)
    {
        if ($this->__isset($name)) {
            unset($this->params[$name]);
        }
        return $this;
    }

    /**
     * Directly dispatches the request
     * 
     * @return \Europa\Controller
     */
    public function dispatch()
    {
        $controller = $this->formatController();
        if (!\Europa\Loader::loadClass($controller)) {
            throw new \Europa\Request\Exception(
                'Could not load controller ' . $controller . '.',
                \Europa\Request\Exception::CONTROLLER_NOT_FOUND
            );
        }
        
        $controller = new $controller($this);
        if (!$controller instanceof \Europa\Controller) {
            throw new \Europa\Request\Exception(
                'Class '
                . get_class($controller) 
                . ' is not a valid controller instance.'
                . 'Controller classes must derive from \Europa\Controller.'
            );
        }
        
        $controller->action();
        return $controller;
    }
    
    /**
     * Sets parameters.
     * 
     * @param mixed $params The params to set.
     * 
     * @return \Europa\Request
     */
    public function setParams($params)
    {
        // check for appropriate parameters
        if (is_array($params) || is_object($params)) {
            foreach ($params as $name => $value) {
                $this->__set($name, $value);
            }
        }
        return $this;
    }
    
    /**
     * Returns the parameters.
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Checks to see if all of the passed params are in the request.
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
     * Removes the parameters.
     * 
     * @return \Europa\Request
     */
    public function removeParams()
    {
        $this->params = array();
        return $this;
    }
    
    /**
     * Merges in the passed parameters.
     * 
     * @param array $params The parameters to merge.
     * 
     * @return \Europa\Request
     */
    public function mergeParams($params)
    {
        // get the old
        $oldParams = $this->params;
        
        // set the new to handle objects as well
        $this->setParams($params);
        
        // get the new
        $newParams = $this->params;
        
        // merge and set
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    /**
     * Sets the controller parameter.
     * 
     * @param string $controller The controller to set.
     * 
     * @return \Europa\Request
     */
    public function setController($controller)
    {
        return $this->__set($this->getControllerKey(), $controller);
    }
    
    /**
     * Returns the controller parameter. This is the value that is passed to the formatter.
     * 
     * @return string
     */
    public function getController()
    {
        return $this->__get($this->getControllerKey());
    }
    
    /**
     * Sets the controller key to use for retrieving it from the request.
     * 
     * @param string $key The key of the controller parameter.
     * 
     * @return \Europa\Request
     */
    public function setControllerKey($newKey)
    {
        // retrieve the current key and controller
        $oldKey = $this->controllerKey;
        $oldVal = $this->__get($oldKey);
        
        // set the new key
        $this->controllerKey = $newKey;
        
        // auto-set the new controller parameter to the old value
        return $this->__set($newKey, $oldVal);
    }
    
    /**
     * Retrieves the controller key.
     * 
     * @return string
     */
    public function getControllerKey()
    {
        return $this->controllerKey;
    }
    
    /**
     * Returns the formatted controller name that should be instantiated.
     * 
     * @return string
     */
    public function formatController()
    {
        if ($this->controllerFormatter) {
            return call_user_func($this->controllerFormatter, $this);
        }
        return \Europa\String::create($this->getController())->toClass() . 'Controller';
    }
    
    /**
     * Sets the formatter that should be used to format the controller class.
     * 
     * @param mixed $callback The callback for formatting the controller.
     * 
     * @return \Europa\Request
     */
    public function setControllerFormatter($callback)
    {
        if (!is_callable($callback, true)) {
            throw new \Europa\Request\Exception(
                'The specified controller formatter is not valid.',
                \Europa\Request\Exception::INVALID_CONTROLLER_FORMATTER
            );
        }
        $this->controllerFormatter = $callback;
        return $this;
    }
    
    /**
     * Returns the parameter count.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->params);
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
     * Returns the parameter key.
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
     * @return \Europa\Request
     */
    public function next()
    {
        next($this->params);
        return $this;
    }
    
    /**
     * Resets iteration.
     * 
     * @return \Europa\Request
     */
    public function rewind()
    {
        reset($this->params);
        return $this;
    }
    
    /**
     * Returns whether or not the iteration can continue.
     * 
     * @return bool
     */
    public function valid()
    {
        return isset($this->params[$this->key()]);
    }
    
    /**
     * Alias for \Europa\Request->set().
     * 
     * @param string $offset The parameter.
     * @param mixed  $value  The value.
     * 
     * @return mixed
     * 
     * @see \Europa\Request->set();
     */
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }
    
    /**
     * Alias for \Europa\Request->get().
     * 
     * @param string $offset The parameter.
     * 
     * @return \Europa\Request
     * 
     * @see \Europa\Request->get();
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }
    
    /**
     * Alias for \Europa\Request->has().
     * 
     * @param string $offset The parameter.
     * 
     * @return mixed
     * 
     * @see \Europa\Request->has();
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }
    
    /**
     * Alias for \Europa\Request->clear().
     * 
     * @param string $offset The parameter.
     * 
     * @return mixed
     * 
     * @see \Europa\Request->clear();
     */
    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }
    
    /**
     * Returns whether or not the request is a CLI request or not.
     * 
     * @return bool
     */
    public static function isCli()
    {
        return defined('STDIN');
    }
}