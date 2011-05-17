<?php

namespace Europa;
use Europa\Controller;
use Europa\Loader;
use Europa\Request\Cli;
use Europa\Request\Exception;
use Europa\Request\Http;
use Europa\String;

/**
 * The heart of EuropaPHP. This is where it all starts and ends.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class Request
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
     * Sets the specified request parameter.
     * 
     * @param string $name  The name of the parameter.
     * @param mixed  $value The value of the parameter.
     * 
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->setParam($name, $value);
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
        return $this->getParam($name);
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
        return $this->hasParam($name);
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
        return $this->removeParam($name);
    }
    
    /**
     * Sets the specified request parameter.
     * 
     * @param string $name  The name of the parameter.
     * @param mixed  $value The value of the parameter.
     * 
     * @return mixed
     */
    public function setParam($name, $value)
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
    public function getParam($name)
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
    public function hasParam($name)
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
    public function removeParam($name)
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
     * Returns the bound parameters.
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Clears request parameters.
     * 
     * @return \Europa\Request
     */
    public function removeParams()
    {
        $this->params = array();
        return $this;
    }
    
    /**
     * Returns the parameters whose names match the regex. The delimitter is automated so you don't have to type it
     * everytime you search. The default delmitter is a forward slash.
     * 
     * @param string $pattern    The pattern to use for searching.
     * @param string $delimitter The delimitter to use for the pattern.
     * 
     * @return array
     */
    public function searchParams($pattern, $delimitter = '/', $flags = null)
    {
        $params  = array();
        $pattern = $delimitter . $pattern . $delimitter . $flags;
        foreach ($this->params as $name => $value) {
            if (preg_match($pattern, $name)) {
                $params[$name] = $value;
            }
        }
        return $params;
    }
    
    /**
     * Sets the appropriate method.
     * 
     * @param string $method The method to set.
     * 
     * @return \Europa\Request
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }
    
    /**
     * Returns the request method from the server vars and formats it. Defaults to "get".
     * It also allows the use of an "HTTP_X_HTTP_METHOD_OVERRIDE" header which can be
     * used to override default request methods. Generally this is bad practice, but
     * certain clients do no support certain methods in the HTTP specification such as
     * Flash.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
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
            throw new Exception('Could not load controller ' . $controller . '.', Exception::CONTROLLER_NOT_FOUND);
        }
        
        $controller = new $controller($this);
        if (!$controller instanceof Controller) {
            throw new Exception(
                'Class ' . get_class($controller)  . ' is not a valid controller instance. Controller classes must '
                . 'derive from \Europa\Controller.'
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
        return 'Controller' . String::create($this->getController())->toClass();
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
            throw new Exception(
                'The specified controller formatter is not valid.',
                Exception::INVALID_CONTROLLER_FORMATTER
            );
        }
        $this->controllerFormatter = $callback;
        return $this;
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
    
    /**
     * Creates a new instance of the statically called request.
     * 
     * @return \Europa\Request
     */
    public static function create()
    {
        return new static;
    }
}