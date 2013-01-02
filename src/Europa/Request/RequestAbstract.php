<?php

namespace Europa\Request;
use ArrayIterator;
use IteratorAggregate;

abstract class RequestAbstract implements IteratorAggregate, RequestInterface
{
    private $id;
    
    private $method;
    
    private $params = [];

    public function __set($name, $value)
    {
        return $this->setParam($name, $value);
    }
    
    public function __get($name)
    {
        return $this->getParam($name);
    }
    
    public function __isset($name)
    {
        return $this->hasParam($name);
    }
    
    public function __unset($name)
    {
        return $this->removeParam($name);
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }
    
    public function getParam($name, $default = null)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return $default;
    }
    
    public function hasParam($name)
    {
        return array_key_exists($name, $this->params);
    }
    
    public function removeParam($name)
    {
        if (isset($this->params[$name])) {
            unset($this->params[$name]);
        }
        return $this;
    }
    
    public function setParams($params)
    {
        if (is_array($params) || is_object($params)) {
            foreach ($params as $name => $value) {
                $this->__set($name, $value);
            }
        }
        return $this;
    }
    
    public function getParams()
    {
        return $this->params;
    }
    
    public function removeParams()
    {
        $this->params = array();
        return $this;
    }
    
    public function searchParams($pattern, $delimiter = '/', $flags = null)
    {
        $params  = array();
        $pattern = $delimiter . $pattern . $delimiter . $flags;
        foreach ($this->params as $name => $value) {
            if (preg_match($pattern, $name)) {
                $params[$name] = $value;
            }
        }
        return $params;
    }
    
    public function getId()
    {
        if (!$this->id) {
            $this->id = md5(uniqid(rand(), true));
        }
        return $this->id;
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->params);
    }
    
    public function serialize()
    {
        return serialize($this->getParams());
    }
    
    public function unserialize($serialized)
    {
        return $this->setParams(unserialize($serialized));
    }
    
    public static function isCli()
    {
        return PHP_SAPI === 'cli';
    }

    public static function detect()
    {
        return self::isCli() ? new Cli : new Http;
    }
}