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
     * Whether or not the object is readonly.
     * 
     * @var bool
     */
    private $readonly = false;

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

    /**
     * Gets an option using array syntax.
     * 
     * @param string $name The option name.
     * 
     * @return mixed
     */
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

    /**
     * Checks if an option is set using array syntax.
     * 
     * @param string $name The option name.
     * 
     * @return bool
     */
    public function offsetExists($name)
    {
        $parts = $this->parseOptionName($name);
        return $parts['nested'] ? $parts['config']->offsetExists($parts['name']) : isset($this->config[$name]);
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

        $parts = $this->parseOptionName($name);

        if ($parts['nested']) {
            $parts['config']->offsetUnset($parts['name']);
        } elseif (isset($this->config[$name])) {
            unset($this->config[$name]);
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
        $this->readonly = $switch ? true : false;
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

    /**
     * Parses the option value and allows the referencing of other options.
     * 
     * @param mixed $value The value to parse.
     * 
     * @return mixed
     */
    private function parseOptionValue($value)
    {
        if (is_string($value) && isset($value[0])) {
            if ($value[0] === '=') {
                preg_match_all('/\{([^{]+)\}/', $value, $holders);

                foreach ($holders[1] as $holder) {
                    $value = str_replace('{' . $holder . '}', $this->offsetGet($holder), $value);
                }

                $value = substr($value, 1);
            } elseif ($value[0] === '\\' && $value[1] === '=') {
                $value = substr($value, 1);
            }
        }

        return (new FromStringFilter)->__invoke($value);
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