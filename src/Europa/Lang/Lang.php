<?php

namespace Europa\Lang;
use ArrayIterator;
use Europa\Lang\Adapter\Ini;
use InvalidArgumentException;

class Lang implements LangInterface
{
    private $vars = [];

    public function __call($name, array $args = [])
    {
        if (!isset($this->vars[$name])) {
            return;
        }

        if (!$args) {
            return $this->vars[$name];
        }

        if (!is_array($args[0])) {
            return vsprintf($this->vars[$name], $args);
        }

        $value = $this->vars[$name];

        foreach ($args[0] as $pos => $arg) {
            $value = str_replace(':' . $pos, $arg, $value);
        }

        return $value;
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
        $this->vars[$name] = $value;
        return $this;
    }

    public function offsetGet($name)
    {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        }
    }

    public function offsetExists($name)
    {
        return isset($this->vars[$name]);
    }

    public function offsetUnset($name)
    {
        if (isset($this->vars[$name])) {
            unset($this->vars[$name]);
        }
        return $this;
    }

    public function count()
    {
        return count($this->vars);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->vars);
    }

    public function add(array $vars)
    {
        $this->vars = array_merge($this->vars, $vars);
        return $this;
    }

    public function addAdapter($vars)
    {
        if (!is_callable($vars)) {
            throw new InvalidArgumentException('The specified language adapter is not callable.');
        }
        return $this->add($vars());
    }
}