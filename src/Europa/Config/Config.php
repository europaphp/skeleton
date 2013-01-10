<?php

namespace Europa\Config;
use ArrayIterator;
use Europa\Exception\Exception;
use Europa\Filter\FromStringFilter;

class Config implements ConfigInterface
{
    const REGEX_VALID_NAME = '[a-zA-Z_][a-zA-Z0-9_.]*';

    const RESERVED_NAME_PARENT = '_parent';

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
            $value = new static($value);
        }

        if ($value instanceof ConfigInterface) {
            $value->setParent($this);
        }

        extract($this->parseOptionName($name));

        $config = $this;

        foreach ($first as $name) {
            $config = $config->createIfNotExists($name);
        }

        if ($first) {
            $config->offsetSet($last, $value);
        } else {
            $this->config[$last] = $value;
        }

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

        extract($this->parseOptionName($name));

        if ($config) {
            return $this->parseOptionValue($config->offsetGet($last));
        }
    }

    public function offsetExists($name)
    {
        if (array_key_exists($name, $this->config)) {
            return true;
        }

        if ($name === self::RESERVED_NAME_PARENT) {
            return isset($this->parent);
        }

        extract($this->parseOptionName($name));

        if ($config) {
            return $config->offsetExists($last);
        }
    }

    public function offsetUnset($name)
    {
        $this->checkReadonly();

        if (array_key_exists($name, $this->config)) {
            unset($this->config[$name]);
        }

        extract($this->parseOptionName($name));

        if ($config) {
            $config->offsetUnset($last);
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

            $config = new $adapter($config);
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
            if ($value instanceof ConfigInterface) {
                $config[$name] = $value->export();
            } else {
                $config[$name] = $this->offsetGet($name);
            }
        }

        return $config;
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

    public function createIfNotExists($name)
    {
        if (!isset($this->config[$name]) || !$this->config[$name] instanceof ConfigInterface) {
            $this->config[$name] = new static;
            $this->config[$name]->setParent($this);
        }

        return $this->config[$name];
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

    private function parseOptionName($name)
    {
        $all    = explode('.', $name);
        $first  = $all;
        $last   = array_pop($first);
        $config = $this;

        foreach ($first as $name) {
            if ($config instanceof ConfigInterface) {
                $config = $config->offsetGet($name);
            } else {
                break;
            }
        }

        if ($config === $this || !$config instanceof ConfigInterface) {
            $config = false;
        }

        return [
            'first'  => $first,
            'last'   => $last,
            'all'    => $all,
            'config' => $config
        ];
    }

    private function parseOptionValue($value)
    {
        if ($this->evaluate && $this->isParsable($value)) {
            $value = eval('return "' . $this->convertValueToPhp($value) . '";');
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
            $replace = str_replace('.', '->', $replace);
            return str_replace('{', '{$this->', $replace);
        }, $value);
    }

    private function checkReadonly()
    {
        if ($this->readonly) {
            Exception::toss('Cannot modify the configuration because it is readonly.');
        }
    }
}