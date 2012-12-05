<?php

namespace Europa\Config;
use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * Config blueprint.
 * 
 * @category Config
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface ConfigInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Sets an option using object syntax.
     * 
     * @param string $name  The option name.
     * @param mixed  $value The option value.
     * 
     * @return Config
     */
    public function __set($name, $value);

    /**
     * Gets an option using object syntax.
     * 
     * @param string $name The option name.
     * 
     * @return mixed
     */
    public function __get($name);

    /**
     * Checks if an option is set using object syntax.
     * 
     * @param string $name The option name.
     * 
     * @return bool
     */
    public function __isset($name);

    /**
     * Removes an option using object syntax.
     * 
     * @param string $name The option name.
     * 
     * @return Config
     */
    public function __unset($name);

    /**
     * Imports values.
     * 
     * @param mixed $config The configuration to import. Accepts a string (path), callable (returns array) or any traversable item.
     * 
     * @return Config
     */
    public function import($config);

    /**
     * Exports the options to a raw array.
     * 
     * @return array
     */
    public function export();

    /**
     * Sets the parent of this object.
     * 
     * @param ConfigInterface $config The config parent.
     * 
     * @return Config
     */
    public function setParent(ConfigInterface $config);

    /**
     * Returns the parent configuration.
     * 
     * @return ConfigInterface | null
     */
    public function getParent();

    /**
     * Returns the root config object which can be the current object.
     * 
     * @return ConfigInterface | null
     */
    public function getRootParent();
}