<?php

namespace Europa\Config;
use ArrayIterator;
use Europa\Exception\Exception;
use Europa\Filter\FromStringFilter;

class Config implements ConfigInterface
{
    const ADAPTER_FROM_NS = '\Adapter\From\\';

    const ADAPTER_TO_NS = '\Adapter\To\\';

    const REGEX_VALID_NAME = '[a-zA-Z_][a-zA-Z0-9_.]*';

    const RESERVED_NAME_PARENT = '_parent';

    const RESERVED_NAME_ROOT = '_root';

    private $config = [];

    private $parent;

    private $readonly = false;

    private $evaluate = true;

    public function __construct()
    {
        foreach (func_get_args() as $config) {
            $this->import($config);
        }
    }

    public function __toString()
    {
        return $this->serialize();
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

        if (is_array($value) || is_object($value)) {
            if (!$value instanceof ConfigInterface) {
                $value = new static($value);
            }
            
            $value->setParent($this);
        }

        $this->config[$name] = $value;

        return $this;
    }

    public function offsetGet($name)
    {
        if (array_key_exists($name, $this->config)) {
            return $this->parseOptionValue($this->config[$name]);
        }

        if ($name === self::RESERVED_NAME_PARENT) {
            return $this->getParent();
        }

        if ($name === self::RESERVED_NAME_ROOT) {
            return $this->getRootParent();
        }
    }

    public function offsetExists($name)
    {
        if (array_key_exists($name, $this->config)) {
            return true;
        }

        if ($name === self::RESERVED_NAME_PARENT || $name === self::RESERVED_NAME_ROOT) {
            return isset($this->parent);
        }
    }

    public function offsetUnset($name)
    {
        $this->checkReadonly();

        if (array_key_exists($name, $this->config)) {
            unset($this->config[$name]);
        }
    }

    public function count()
    {
        return count($this->config);
    }

    public function current()
    {
        return $this->parseOptionValue(current($this->config));
    }

    public function key()
    {
        return key($this->config);
    }

    public function next()
    {
        next($this->config);
    }

    public function rewind()
    {
        reset($this->config);
    }

    public function valid()
    {
        return key($this->config) !== null;
    }

    public function serialize()
    {
        return serialize($this->config);
    }

    public function unserialize($data)
    {
        $this->config = unserialize($data);
    }

    public function import($config, $adapter = null)
    {
        $this->checkReadonly();

        if (is_string($config)) {
            $config = $this->parseConfigFromString($config, $adapter);
        }

        if (is_callable($config)) {
            $config = $config();
        }

        if ($config instanceof ConfigInterface) {
            $config = $config->raw();
        }

        if (is_array($config) || is_object($config)) {
            foreach ($config as $name => $value) {
                if (isset($this->config[$name]) && $this->config[$name] instanceof ConfigInterface) {
                    $this->config[$name]->import($value);
                } else {
                    $this->offsetSet($name, $value);
                }
            }
        }

        return $this;
    }

    public function export($adapter = null)
    {
        $config = [];
        
        foreach ($this as $name => $value) {
            if ($value instanceof ConfigInterface) {
                $config[$name] = $value->export();
            } else {
                $config[$name] = $this->offsetGet($name);
            }
        }

        if (is_string($adapter)) {
            $adapter = __NAMESPACE__ . self::ADAPTER_TO_NS . ucfirst($adapter);
            $adapter = new $adapter;
        }

        if (is_callable($adapter)) {
            $config = $adapter($config);
        }

        return $config;
    }

    public function raw()
    {
        return $this->config;
    }

    public function clear()
    {
        $this->checkReadonly();
        $this->config = [];
        return $this;
    }

    public function setParent(ConfigInterface $config)
    {
        $this->parent = $config;
        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getRootParent()
    {
        $config = $this->getParent();

        while ($parent = $config->getParent()) {
            $config = $parent;
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
        $this->readonly = $switch ?: false;
        return $this;
    }

    public function evaluate($switch = true)
    {
        $this->evaluate = $switch ?: false;
        return $this;
    }

    private function parseOptionValue($value)
    {
        if ($this->evaluate && $this->isParsable($value)) {
            ob_start();
            
            $parsed = eval('return "' . $this->convertValueToPhp($value) . '";');
            
            if ($error = ob_get_clean()) {
                Exception::toss('Unable to parse configuration value "%s"', $value, $error);
            }

            return $parsed;
        }

        return $value;
    }

    private function isParsable($value)
    {
        return is_string($value) && strpos($value, '{') !== false && strpos($value, '}') !== false;
    }

    private function convertValueToPhp($value)
    {
        $value = str_replace('"', '\"', $value);
        $value = str_replace('\\', '\\\\', $value);
        return preg_replace_callback('/\{' . self::REGEX_VALID_NAME . '\}/', function($replace) {
            $replace = $replace[0];
            $replace = str_replace('.', '\'][\'', $replace);
            $replace = substr($replace, 0, -1);
            $replace = str_replace('{', '{$this[\'', $replace);
            return $replace . '\']}';
        }, $value);
    }

    private function checkReadonly()
    {
        if ($this->readonly) {
            Exception::toss('Cannot modify the configuration because it is readonly.');
        }
    }

    private function parseConfigFromString($config, $adapter)
    {
        if (!$adapter) {
            if (is_file($config)) {
                $adapter = $this->autodetectAdapterFromFile($config);
                $config  = file_get_contents($config);
            } else {
                Exception::toss('Could not import "%s" because the file does not exist.', $config);
            }
        }

        $adapter = __NAMESPACE__ . self::ADAPTER_FROM_NS . ucfirst($adapter);

        if (!class_exists($adapter)) {
            Exception::toss('Could not import "%s" because the config adapter "%s" does not exist.', $config, $adapter);
        }

        $adapter = new $adapter;

        return $adapter($config);
    }

    private function autodetectAdapterFromFile($config)
    {
        return pathinfo($config, PATHINFO_EXTENSION);
    }
}