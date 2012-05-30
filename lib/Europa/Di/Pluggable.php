<?php

namespace Europa\Di;
use Europa\Filter\ClassNameFilter;
use LogicException;
use ReflectionClass;

/**
 * Adds plugin functionality to a class.
 * 
 * @category Di
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
trait Pluggable
{
    /**
     * The container used to get plugins.
     * 
     * @var Container
     */
    private $container;
    
    /**
     * Calls a helper from the specified container.
     * 
     * @param string $name   The name of the helper to create.
     * @param array  $params The arguments to configure it with.
     * 
     * @throws LogicException If the container does not exist.
     * 
     * @return mixed
     */
    public function __call($name, array $params = array())
    {
        return $this->getPluginContainer()->resolve($name)->create($params);
    }
    
    /**
     * Calls a helper from the specified container.
     * 
     * @param string $name The name of the helper to create.
     * 
     * @throws LogicException If the container does not exist.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getPluginContainer()->resolve($name)->get();
    }
    
    /**
     * Returns whether or not there is a container and the specified plugin exists.
     * 
     * @param string $name The name of the plugin to check for.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return $this->getPluginContainer()->resolve($name)->exists();
    }
    
    /**
     * Clears a plugin instance from the container.
     * 
     * @return void
     */
    public function __unset($name)
    {
        $this->getPluginContainer()->resolve($name)->refresh();
    }
    
    /**
     * Sets the DI container to use.
     * 
     * @param Container $container The DI contaienr to use.
     * 
     * @return Container
     */
    public function setPluginContainer(Container $container)
    {
        $this->container = $container;
        
        return $this;
    }
    
    /**
     * Ensures that a plugin container exists and returns it.
     * 
     * @return Container
     */
    public function getPluginContainer()
    {
        if (!$this->container) {
            $this->container = new Container;
        }
        
        return $this->container;
    }
}