<?php

namespace Europa\Config;
use ArrayIterator;
use Europa\Exception\Exception;

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
        $this->checkReadonly();

        $name  = $name ?: count($this->config);
        $parts = $this->parseOptionName($name);
        
        if ($parts['nested']) {
            $parts['config']->offsetSet($parts['name'], $value);
        } else {
            if (is_array($value) || is_object($value)) {
                $value = new static($value);
            }

            $this->config[$name] = $value;
        }

        return $this;
    }

    public function offsetGet($name)
    {
        $parts = $this->parseOptionName($name);

        if ($parts['nested']) {
            $value = $parts['config']->offsetGet($parts['name']);
        } elseif (array_key_exists($name, $this->config)) {
            $value = $this->config[$name];
        } else {
            $value = $this->config[$name] = new static(['parent' => $this]);
        }

        $value = $this->parseOptionValue($value);

        return $value;
    }

    public function offsetExists($name)
    {
        $parts = $this->parseOptionName($name);
        return $parts['nested'] ? $parts['config']->offsetExists($parts['name']) : isset($this->config[$name]);
    }

    public function offsetUnset($name)
    {
        $this->checkReadonly();

        $parts = $this->parseOptionName($name);

        if ($parts['nested']) {
            $parts['config']->offsetUnset($parts['name']);
        } elseif (isset($this->config[$name])) {
            unset($this->config[$name]);
        }

        return $this;
    }

    public function count()
    {
        return count($this->config);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->config);
    }

    public function import($config)
    {
        $this->checkReadonly();
        
        if (is_string($config)) {
            $adapter = pathinfo($config, PATHINFO_EXTENSION);
            $adapter = 'Europa\Config\Adapter\\' . ucfirst($adapter);

            if (!class_exists($adapter)) {
                Exception::toss('The config adapter "%s" does not exist.', $adapter);
            }

            return $this->import(new $adapter($config));
        }

        if (is_callable($config)) {
            $config = $config();
        }

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
            if ($name === 'parent') {
                continue;
            }

            if ($value instanceof static) {
                $config[$name] = $value->export();
            } else {
                $config[$name] = $this->offsetGet($name);
            }
        }

        return $config;
    }

    public function keys()
    {
        return array_keys($this->config);
    }

    public function values()
    {
        return array_values($this->config);
    }

    public function readonly($switch = true)
    {
        $this->readonly = $switch ? true : false;
        return $this;
    }

    private function parseOptionName($name)
    {
        if (strpos($name, '.') !== false) {
            $names  = explode('.', $name);
            $name   = array_pop($names);
            $config = $this;

            foreach ($names as $part) {
                $config = $config->offsetGet($part);

                if (!$config instanceof ConfigInterface) {
                    Exception::toss('Dot-notated configuration value part "%s" of "%s" must be a configuration object.', $part, implode('.', $names) . '.' . $name);
                }
            }

            return [
                'name'   => $name,
                'config' => $config,
                'nested' => true
            ];
        }

        return ['nested' => false];
    }

    private function parseOptionValue($value)
    {
        if (is_string($value) && isset($value[0]) && $value[0] === '=') {
            preg_match_all('/\{([^{]+)\}/', $value, $holders);

            foreach ($holders[1] as $holder) {
                $value = str_replace('{' . $holder . '}', $this->offsetGet($holder), $value);
            }

            $value = substr($value, 1);
        }

        return $value;
    }

    private function checkReadonly()
    {
        if ($this->readonly) {
            Exception::toss('Cannot modify the configuration because it is readonly.');
        }
    }
}