<?php

namespace Europa\Config;
use ArrayIterator;
use Europa\Exception\Exception;
use Europa\Filter\FromStringFilter;

/**
 * Easy, fluid way of manipulating configuration arrays.
 * 
 * @category Config
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Config implements ConfigInterface
{
    /**
     * The raw configuration data.
     * 
     * @var array
     */
    private $config = [];

    /**
     * The parent configuration object.
     * 
     * @var ConfigInterface
     */
    private $parent;

    /**
     * Whether or not the object is readonly.
     * 
     * @var bool
     */
    private $readonly = false;

    /**
     * Whether or not to evaluate each value as PHP to reference other strings.
     * 
     * @var bool
     */
    private $evaluate = true;

    /**
     * Sets up a new config object. Each argument is imported in the order it is specified. This means, latter
     * arguments overwrite former arguments if there are any conflicts.
     * 
     * @return Config
     */
    public function __construct()
    {
        foreach (func_get_args() as $config) {
            $this->import($config);
        }
    }

    /**
     * Sets an option using object syntax.
     * 
     * @param string $name  The option name.
     * @param mixed  $value The option value.
     * 
     * @return Config
     */
    public function __set($name, $value)
    {
        return $this->offsetSet($name, $value);
    }

    /**
     * Gets an option using object syntax.
     * 
     * @param string $name The option name.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * Checks if an option is set using object syntax.
     * 
     * @param string $name The option name.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * Removes an option using object syntax.
     * 
     * @param string $name The option name.
     * 
     * @return Config
     */
    public function __unset($name)
    {
        return $this->offsetUnset($name);
    }

    /**
     * Sets an option using array syntax.
     * 
     * @param string $name  The option name.
     * @param mixed  $value The option value.
     * 
     * @return Config
     */
    public function offsetSet($name, $value)
    {
        $this->checkReadonly();

        if ($value instanceof ConfigInterface) {
            $value->setParent($this);
        } elseif (is_array($value) || is_object($value)) {
            $value = new static($value);
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

    /**
     * Gets an option using array syntax.
     * 
     * @param string $name The option name.
     * 
     * @return mixed
     */
    public function offsetGet($name)
    {
        if (array_key_exists($name, $this->config)) {
            return $this->parseOptionValue($this->config[$name]);
        }

        extract($this->parseOptionName($name));

        if ($config) {
            return $this->parseOptionValue($config->offsetGet($last));
        }
    }

    /**
     * Checks if an option is set using array syntax.
     * 
     * @param string $name The option name.
     * 
     * @return bool
     */
    public function offsetExists($name)
    {
        if (array_key_exists($name, $this->config)) {
            return true;
        }

        extract($this->parseOptionName($name));

        if ($config) {
            return $config->offsetExists($last);
        }
    }

    /**
     * Removes an option using array syntax.
     * 
     * @param string $name The option name.
     * 
     * @return Config
     */
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

    /**
     * Returns the number of options.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->config);
    }

    /**
     * Returns an iterator for the options.
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->config);
    }

    /**
     * Imports values.
     * 
     * @param mixed $config The configuration to import. Accepts a string (path), callable (returns array) or any traversable item.
     * 
     * @return Config
     */
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

    /**
     * Exports the options to a raw array.
     * 
     * @return array
     */
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

    /**
     * Sets the parent of this object.
     * 
     * @param ConfigInterface $config The config parent.
     * 
     * @return Config
     */
    public function setParent(ConfigInterface $config)
    {
        $this->parent = $config;
        return $this;
    }

    /**
     * Returns the parent configuration.
     * 
     * @return ConfigInterface | null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns the root config object which can be the current object.
     * 
     * @return ConfigInterface | null
     */
    public function getRootParent()
    {
        $config = $this->getParent();

        while ($parent = $config->getParent()) {
            $config = $parent;
        }

        return $config;
    }

    /**
     * Creates the specified key if it doesn't exist with a new config object and returns it.
     * 
     * @param string $name The key name.
     * 
     * @return ConfigInterface
     */
    public function createIfNotExists($name)
    {
        if (!isset($this->config[$name]) || !$this->config[$name] instanceof ConfigInterface) {
            $this->config[$name] = new static;
            $this->config[$name]->setParent($this);
        }

        return $this->config[$name];
    }

    /**
     * Returns the option keys.
     * 
     * @return array
     */
    public function keys()
    {
        return array_keys($this->config);
    }

    /**
     * Returns the option values.
     * 
     * @return array
     */
    public function values()
    {
        return array_values($this->config);
    }

    /**
     * Marks the configuration as readonly.
     * 
     * @param bool $switch If true, it makes the config writable; if false, it makes it readonly.
     * 
     * @return Config
     */
    public function readonly($switch = true)
    {
        $this->readonly = $switch ?: false;
        return $this;
    }

    /**
     * Whether or not we should evaluate values.
     * 
     * @param bool $switch `True` evaluates values. `False` doesn't.
     * 
     * @return Config
     */
    public function evaluate($switch = true)
    {
        $this->evaluate = $switch ?: false;
        return $this;
    }

    /**
     * Parses the option name and returns information about it.
     * 
     * @param string $name The option name.
     * 
     * @return array
     */
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

    /**
     * Evaluates the value as a PHP string.
     * 
     * @param mixed $value The value to parse.
     * 
     * @return mixed
     */
    private function parseOptionValue($value)
    {
        if ($this->evaluate && is_string($value)) {
            $value = str_replace('"', '\"', $value);
            $value = str_replace('\\', '\\\\', $value);
            $value = eval('return "' . $value . '";');
        }

        return $value;
    }

    /**
     * Checks the object if it is readonly and throws an exception if it is.
     * 
     * @throws Exception If the config object cannot be modified.
     * 
     * @return void
     */
    private function checkReadonly()
    {
        if ($this->readonly) {
            Exception::toss('Cannot modify the configuration because it is readonly.');
        }
    }
}