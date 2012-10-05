<?php

namespace Europa\Config;
use ArrayIterator;
use LogicException;

class Config implements ConfigInterface
{
    private $config = [];

    private $readonly = false;

    public function __construct()
    {
        foreach (func_get_args() as $config) {
            $this->import($config);
        }
    }

    public function __set($name, $value)
    {
        return $this->offsetSet($name, $value);
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    public function __unset($name)
    {
        return $this->offsetUnset($name);
    }

    public function offsetSet($name, $value)
    {
        if (!$name) {
            $name = count($this->config);
        }
        
        if ($this->readonly) {
            throw new LogicException(sprintf(
                'Cannot modify configuration "%s" because it is set as readonly.',
                get_class($this)
            ));
        }

        if (is_array($value) || is_object($value)) {
            $value = new static($value);
        }

        if (strpos($name, '.') !== false) {
            $names  = explode('.', $name);
            $last   = array_pop($names);
            $config = $this;

            foreach ($names as $name) {
                $config = $config->offsetGet($name);
            }

            $config->offsetSet($last, $value);
        } else {
            $this->config[$name] = $value;
        }

        return $this;
    }

    public function offsetGet($name)
    {
        if (!array_key_exists($name, $this->config)) {
            $this->config[$name] = new static(['parent' => $this]);
        }

        $value = $this->config[$name];
        $value = $this->parse($value);

        return $value;
    }

    public function offsetExists($name)
    {
        return isset($this->config[$name]);
    }

    public function offsetUnset($name)
    {
        if (isset($this->config[$name])) {
            unset($this->config[$name]);
        }

        return $this;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->config);
    }

    public function import($config)
    {
        if (is_array($config) || is_object($config)) {
            foreach ($config as $name => $value) {
                $this->offsetSet($name, $value);
            }
        }

        return $this;
    }

    public function export()
    {
        $config = [];
        
        foreach ($this->config as $name => $value) {
            if ($value instanceof static) {
                $config[$name] = $value->export();
            } else {
                $config[$name] = $this->offsetGet($name);
            }
        }

        return $config;
    }

    public function readonly($switch = true)
    {
        $this->readonly = $switch ? true : false;
        return $this;
    }

    private function parse($value)
    {
        if (is_string($value) && $value[0] === '=') {
            preg_match_all('/\{([^{]+)\}/', $value, $holders);

            foreach ($holders[1] as $holder) {
                $value = str_replace('{' . $holder . '}', $this->offsetGet($holder), $value);
            }

            $value = substr($value, 1);
        }

        return $value;
    }
}