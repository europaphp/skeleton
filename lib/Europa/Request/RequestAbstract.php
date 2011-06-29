<?php

namespace Europa\Request;
use Europa\Controller\ControllerAbstract;
use Europa\Loader;
use Europa\StringObject;

/**
 * The main request object.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class RequestAbstract implements \Serializable
{
    /**
     * The request unique id.
     * 
     * @var string
     */
    private $id;
    
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
     * Serializes the request.
     * 
     * @return string
     */
    public function serialize()
    {
        return serialize($this->getParams());
    }
    /**
     * Unserializes the request.
     * 
     * @param string $serialized The serialized string.
     * 
     * @return \Europa\Request
     */
    public function unserialize($serialized)
    {
        return $this->setParams(unserialize($serialized));
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
     * Returns the request method for the request.
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
        $controller = new $controller($this);
        if (!$controller instanceof ControllerAbstract) {
            throw new Exception(
                'Class ' . get_class($controller)  . ' is not a valid controller instance. Controller classes must '
                . 'derive from \Europa\ControllerAbstract.'
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
        return 'Controller' . StringObject::create($this->getController())->toClass();
    }
    
    /**
     * Returns the unique request id of the current request. This is useful for debugging separate logs and probably
     * many other things.
     * 
     * @return string
     */
    public function getId()
    {
        if (!$this->id) {
            $this->id = md5(uniqid(rand(), true));
        }
        return $this->id;
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

    /**
     * Auto-detects the request type and returns the appropriate request instance.
     * 
     * @return \Europa\Request
     */
    public static function autoDetect()
    {
        if (static::isCli()) {
            return new Cli;
        }
        return new Http;
    }
}
