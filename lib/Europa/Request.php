<?php

namespace Europa;
use Europa\Controller;
use Europa\Loader;
use Europa\Request\Exception;

/**
 * The heart of EuropaPHP. This is where it all starts and ends.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class Request implements \Iterator, \Countable
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
     * Returns the specified request parameter.
     * 
     * @param string $name The name of the parameter.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return null;
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
        if (isset($this->params[$name])) {
            unset($this->params[$name]);
        }
        return $this;
    }
    
    /**
     * Bulk sets parameters.
     * 
     * @param mixed $params The params to set.
     * 
     * @return \Europa\Request
     */
    public function setParams($params)
    {
        if (is_array($params) || is_object($params)) {
            foreach ($params as $name => $value) {
                $this->__set($name, $value);
            }
        }
        return $this;
    }
    
    /**
     * Clears request parameters.
     * 
     * @return \Europa\Request
     */
    public function clear()
    {
        $this->params = array();
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
        if (!Loader::load($controller)) {
            throw new Exception(
                'Could not load controller ' . $controller . '.',
                Exception::CONTROLLER_NOT_FOUND
            );
        }
        
        $controller = new $controller($this);
        if (!$controller instanceof Controller) {
            throw new Exception(
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
     * Returns whether or not the request is a CLI request or not.
     * 
     * @return bool
     */
    public static function isCli()
    {
        return defined('STDIN');
    }
}