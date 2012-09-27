<?php

namespace Europa\Config;
use ArrayIterator;
use LogicException;

class Config implements ConfigInterface
{
    private $config = [];

    private $readonly = false;

    public function __construct($config = [])
    {
        $this->import($config);
    }

    public function __set($name, $value)
    {
        if ($this->readonly) {
            throw new LogicException(sprintf(
                'Cannot modify configuration "%s" because it is set as readonly.',
                get_class($this)
            ));
        }

        if (is_array($value) || is_object($value)) {
            $value = new static($value);
        }

        $this->config[$name] = $value;

        return $this;
    }

    public function __get($name)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
    }

    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    public function __unset($name)
    {
        if (isset($this->config[$name])) {
            unset($this->config[$name]);
        }
        return $this;
    }

    public function offsetSet($name, $value)
    {
        return $this->__set($name, $value);
    }

    public function offsetGet($name)
    {
        return $this->__get($name);
    }

    public function offsetExists($name)
    {
        return $this->__isset($name);
    }

    public function offsetUnset($name)
    {
        return $this->__unset($name);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->config);
    }

    public function import($config)
    {
        if (is_array($config) || is_object($config)) {
            foreach ($config as $name => $value) {
                $this->__set($name, $value);
            }
        }
        return $this;
    }

    public function export()
    {
        $config = [];
        
        foreach ($this->config as $name => $value) {
            if ($value instanceof static) {
                $value = $value->toArray();
            }

            $config[$name] = $value;
        }

        return $config;
    }

    public function readonly($switch = true)
    {
        $this->readonly = $switch ? true : false;
        return $this;
    }
}