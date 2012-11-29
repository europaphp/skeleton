<?php

namespace Europa\Module;
use Europa\Config\Config;
use Europa\Di\ServiceContainerInterface;

/**
 * Handles the management of multiple modules.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Manager implements ManagerInterface
{
    /**
     * The service container used for dependnecies.
     * 
     * @var ServiceContainerInterface
     */
    private $container;

    /**
     * List of modules to manage.
     * 
     * @var array
     */
    private $modules = [];

    /**
     * Sets up the module manager.
     * 
     * @param ServiceContainerInterface $container The container to use for setting up modules.
     * 
     * @return Manager
     */
    public function __construct(ServiceContainerInterface $container)
    {
        $this->setServiceContainer($container);
    }

    /**
     * Sets the service container the manager should use.
     * 
     * @return Manager
     */
    public function setServiceContainer(ServiceContainerInterface $container)
    {
        $this->container = $container->mustProvide('Europa\Module\ManagerConfigurationInterface');
        return $this;
    }

    /**
     * Returns the service container bound to the module manager.
     * 
     * @return ServiceContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->container;
    }

    /**
     * Bootstraps the modules.
     * 
     * @return Manager
     */
    public function bootstrap()
    {
        foreach ($this->modules as $module) {
            $module($this);
        }
        
        return $this;
    }

    /**
     * Registers a list of modules. Can be anything that can be passed to Europa\Config\Config::import().
     * 
     * @param mixed $modules The modules to add.
     * 
     * @return Manager
     */
    public function register($modules)
    {
        foreach (new Config($modules) as $name => $module) {
            $this->offsetSet($name, $module);
        }

        return $this;
    }

    /**
     * Registers a module.
     * 
     * @param mixed $offset The module index.
     * @param mixed $module The module to register.
     * 
     * @return App
     */
    public function offsetSet($offset, $module)
    {
        if (is_string($module)) {
            $offset = $module;
            $module = [];
        }

        if (!is_callable($module)) {
            $module = new Module($this->container->config->appPath . '/' . $offset, $module);
        }

        $this->modules[$offset] = $module;

        return $this;
    }

    /**
     * Returns the specified module or throws an exception if it does not exist.
     * 
     * @param mixed $offset The module offset.
     * 
     * @return ModuleInterface
     * 
     * @throws LogicException If the module does not exist.
     */
    public function offsetGet($offset)
    {
        if (isset($this->modules[$offset])) {
            return $this->modules[$offset];
        }

        Exception::toss('The module at offset "%s" does not exist.', $offset);
    }

    /**
     * Returns whether or not the module exists.
     * 
     * @param mixed $offset The module offset.
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->modules[$offset]);
    }

    /**
     * Removes the module if it exists.
     * 
     * @param mixed $offset The module offset.
     * 
     * @return bool
     */
    public function offsetUnset($offset)
    {
        if (isset($this->modules[$offset])) {
            unset($this->modules[$offset]);
        }

        return $this;
    }

    /**
     * Returns the number of modules.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->modules);
    }

    /**
     * Returns an iteartor containing the modules
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->modules);
    }
}