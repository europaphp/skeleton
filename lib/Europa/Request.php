<?php

/**
 * The heart of EuropaPHP. This is where it all starts and ends.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class Europa_Request implements Iterator, ArrayAccess, Countable
{
    /**
     * The key used to get the controller from the request params.
     * 
     * @var string
     */
    private $_controllerKey = 'controller';
    
    /**
     * The callback to use for formatting the controller parameter.
     * 
     * @var mixed
     */
    private $_controllerFormatter = null;
    
    /**
     * The params parsed out of the route and cascaded through the
     * super-globals. Contains the default controller to use.
     * 
     * @var array
     */
    private $_params = array('controller' => 'index');
    
    /**
     * Contains the instances of all requests that are currently 
     * dispatching in chronological order.
     * 
     * @var array
     */
    private static $_stack = array();
    
    /**
     * Converts the request back into the original string representation.
     * 
     * @return string
     */
    abstract public function __toString();
    
    /**
     * Returns the request method.
     * 
     * @return string
     */
    abstract public function method();
    
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
            return $this->_params[$name];
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
        $this->_params[$name] = $value;
        return $this;
    }
    
    /**
     * Checks for the specified parameter.
     * 
     * @param string $name The parameter to check for.
     * 
     * @return Europa_Request
     */
    public function __isset($name)
    {
        return isset($this->_params[$name]);
    }
    
    /**
     * Unsets the specified parameter.
     * 
     * @param string $name The parameter to unset.
     * 
     * @return Europa_Request
     */
    public function __unset($name)
    {
        if ($this->__isset($name)) {
            unset($this->_params[$name]);
        }
        return $this;
    }
    
    /**
     * Sets parameters.
     * 
     * @param mixed $params The params to set.
     * 
     * @return Europa_Request
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
        return $this->_params;
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
     * @return Europa_Request
     */
    public function removeParams()
    {
        $this->_params = array();
        return $this;
    }
    
    /**
     * Merges in the passed parameters.
     * 
     * @param array $params The parameters to merge.
     * 
     * @return Europa_Request
     */
    public function mergeParams($params)
    {
        // get the old
        $oldParams = $this->_params;
        
        // set the new to handle objects as well
        $this->setParams($params);
        
        // get the new
        $newParams = $this->_params;
        
        // merge and set
        $this->_params = array_merge($this->_params, $params);
        return $this;
    }
        
    /**
     * Directly dispatches the request
     * 
     * @return Europa_Controller
     */
    public function dispatch()
    {
        // register the instance in the stack so it can be easily found
        self::$_stack[] = $this;
        
        // routing information
        $controller = $this->formatController();
        
        // make sure the controller is loadable
        if (!Europa_Loader::loadClass($controller)) {
            throw new Europa_Request_Exception(
                'Could not load controller ' . $controller . '.',
                Europa_Request_Exception::CONTROLLER_NOT_FOUND
            );
        }
        
        // instantiate the formatted controller
        $controller = new $controller($this);
        
        // make sure it's a valid instance
        if (!$controller instanceof Europa_Controller) {
            throw new Europa_Request_Exception(
                'Class '
                . get_class($controller) 
                . ' is not a valid controller instance.'
                . 'Controller classes must derive from Europa_Controller.'
            );
        }
        
        // execute the rendering process
        $controller->action();
        $rendered = $controller->__toString();
        
        // remove the dispatch from the stack
        array_pop(self::$_stack);
        
        // return the rendered result
        return $rendered;
    }
    
    /**
     * Sets the controller parameter.
     * 
     * @param string $controller The controller to set.
     * 
     * @return Europa_Request
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
     * @return Europa_Request
     */
    public function setControllerKey($newKey)
    {
        // retrieve the current key and controller
        $oldKey = $this->_controllerKey;
        $oldVal = $this->__get($oldKey);
        
        // set the new key
        $this->_controllerKey = $newKey;
        
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
        return $this->_controllerKey;
    }
    
    /**
     * Returns the formatted controller name that should be instantiated.
     * 
     * @return string
     */
    public function formatController()
    {
        if ($this->_controllerFormatter) {
            return call_user_func($this->_controllerFormatter, $this);
        }
        return Europa_String::create($this->getController())->toClass() . 'Controller';
    }
    
    /**
     * Sets the formatter that should be used to format the controller class.
     * 
     * @param mixed $callback The callback for formatting the controller.
     * 
     * @return Europa_Request
     */
    public function setControllerFormatter($callback)
    {
        if (!is_callable($callback, true)) {
            throw new Europa_Request_Exception(
                'The specified controller formatter is not valid.',
                Europa_Request_Exception::INVALID_CONTROLLER_FORMATTER
            );
        }
        $this->_controllerFormatter = $callback;
        return $this;
    }
    
    /**
     * Returns the parameter count.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->_params);
    }
    
    /**
     * Returns the current parameter.
     * 
     * @return mixed
     */
    public function current()
    {
        return current($this->_params);
    }
    
    /**
     * Returns the parameter key.
     * 
     * @return string
     */
    public function key()
    {
        return key($this->_params);
    }
    
    /**
     * Moves to the next parameter.
     * 
     * @return Europa_Request
     */
    public function next()
    {
        next($this->_params);
        return $this;
    }
    
    /**
     * Resets iteration.
     * 
     * @return Europa_Request
     */
    public function rewind()
    {
        reset($this->_params);
        return $this;
    }
    
    /**
     * Returns whether or not the iteration can continue.
     * 
     * @return bool
     */
    public function valid()
    {
        return isset($this->_params[$this->key()]);
    }
    
    /**
     * Alias for Europa_Request->set().
     * 
     * @param string $offset The parameter.
     * @param mixed  $value  The value.
     * 
     * @return mixed
     * 
     * @see Europa_Request->set();
     */
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }
    
    /**
     * Alias for Europa_Request->get().
     * 
     * @param string $offset The parameter.
     * 
     * @return Europa_Request
     * 
     * @see Europa_Request->get();
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }
    
    /**
     * Alias for Europa_Request->has().
     * 
     * @param string $offset The parameter.
     * 
     * @return mixed
     * 
     * @see Europa_Request->has();
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }
    
    /**
     * Alias for Europa_Request->clear().
     * 
     * @param string $offset The parameter.
     * 
     * @return mixed
     * 
     * @see Europa_Request->clear();
     */
    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }
    
    /**
     * Returns the Europa_Request instance that is currently dispatching.
     * 
     * @return mixed
     */
    public static function getCurrent()
    {
        $len = count(self::$_stack);
        if ($len) {
            return self::$_stack[$len - 1];
        }
        return null;
    }
    
    /**
     * Returns all Europa_Request instances that are dispatching, in chronological order, as an array.
     * 
     * @return array
     */
    public static function getStack()
    {
        return self::$_stack;
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