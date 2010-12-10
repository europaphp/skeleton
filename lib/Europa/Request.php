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
        return $this->get($name);
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
        return $this->set($name, $value);
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
        return $this->has($name);
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
        return $this->clear($name);
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
     * Returns a given parameter's value.
     * 
     * @param string $names   The name or names of the parameters to search for.
     * @param mixed  $default The default value to return if the parameters aren't set.
     * 
     * @return mixed
     */
    public function get($names, $default = null)
    {
        if (!is_array($names)) {
            $names = array($names);
        }
        foreach ($names as $name) {
            if (isset($this->_params[$name])) {
                return $this->_params[$name];
            }
        }
        return $default;
    }
    
    /**
     * Sets a given parameter's value. If multiple names are supplied, their
     * values are set to the single passed value. This is useful for example
     * for batch setting of default param values, or in CLI mode when you have
     * a param '--my-param' which is also aliased as 'm'.
     * 
     * @param string $names The parameter name or names.
     * @param mixed  $value The parameter value.
     * 
     * @return Europa_Request
     */
    public function set($names, $value)
    {
        if (!is_array($names)) {
            $names = array($names);
        }
        foreach ($names as $name) {
            $this->_params[$name] = $value;
        }
        return $this;
    }
    
    /**
     * Returns whether or not the specified parameter or parameters exist.
     * 
     * @param mixed $names The name or names of parameters to check for.
     * 
     * @return bool
     */
    public function has($names)
    {
        if (!is_array($names)) {
            $names = array($names);
        }
        foreach ($names as $name) {
            if (isset($this->_params[$name])) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Clears the specified parameter or parameters.
     * 
     * @param mixed $names The name or names of parameters to clear.
     * 
     * @return bool
     */
    public function clear($names)
    {
        if (!is_array($names)) {
            $names = array($names);
        }
        foreach ($nameas as $name) {
            if (isset($this->_params[$name])) {
                unset($this->_params[$name]);
            }
        }
        return $this;
    }
    
    /**
     * Binds multiple parameters to the request. Overrides any existing
     * parameters with the same name.
     * 
     * @param mixed $params The params to set. Can be any iterable value.
     * 
     * @return Europa_Request
     */
    public function setAll($params)
    {
        if (is_array($params) || is_object($params)) {
            foreach ($params as $k => $v) {
                $this->set($k, $v);
            }
        }
        return $this;
    }
    
    /**
     * Returns all parameters set on the request.
     * 
     * @return array
     */
    public function getAll()
    {
        return $this->_params;
    }
    
    /**
     * Clears all parameters from the request. Includes clearing of default values.
     * 
     * @return Europa_Request
     */
    public function clearAll()
    {
        $this->_params = array();
        return $this;
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
        return $this->set($this->getControllerKey(), $controller);
    }
    
    /**
     * Returns the controller parameter. This is the value that is passed to the formatter.
     * 
     * @return string
     */
    public function getController()
    {
        return $this->get($this->getControllerKey());
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
        $oldVal = $this->get($oldKey);
        
        // set the new key
        $this->_controllerKey = $newKey;
        
        // auto-set the new controller parameter to the old value
        return $this->set($newKey, $oldVal);
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
        return $this->set($offset, $value);
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
        return $this->get($offset);
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
        return $this->has($offset);
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
        return $this->clear($offset);
    }
    
    /**
     * Returns the Europa_Request instance that is currently dispatching.
     * 
     * @return mixed
     */
    public static function getLastActive()
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