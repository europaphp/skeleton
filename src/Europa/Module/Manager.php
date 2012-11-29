<?php

namespace Europa\Module;
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
     * @return ModuleManager
     */
    public function __construct(ServiceContainerInterface $container)
    {
        $this->container = $container->mustProvide('Europa\Module\ManagerConfigurationInterface');;
    }

    /**
     * Bootstraps the modules.
     * 
     * @return Manager
     */
    public function bootstrap()
    {
        foreach ($this->modules as $module) {
            foreach ($module->getRequired() as $dep) {
                $this->offsetGet($dep)->bootstrap();
            }

            $name = $module->getName();
            $conf = $module->getConfig();

            $this->container->config->modules->$name->import($conf);
            $this->container->router->import($module->getRoutes());
            $this->container->loaderLocator->addPaths($module->getClassPaths());
            $this->container->viewLocator->addPaths($module->getViewPaths());
            
            if (is_callable($bootstrapper = $module->getBootstrapper())) {
                $bootstrapper($conf);
            }
        }
        
        return $this;
    }

    /**
     * Adds multiple modules at once.
     * 
     * @param mixed $modules The modules to add.
     * 
     * @return ModuleManager
     */
    public function registerAll($modules)
    {
        if (is_array($modules) || is_object($modules)) {
            foreach ($modules as $offset => $module) {
                $this->offsetSet($offset, $module);
            }
        }

        return $this;
    }

    /**
     * Registers a module.
     * 
     * @param mixed                    $offset The module index.
     * @param string | ModuleInterface $module The module to register.
     * 
     * @return App
     */
    public function offsetSet($offset, $module)
    {
        if (!$module instanceof ModuleInterface) {
            $module = new Module($this->container->config->appPath . '/' . $module);
        }

        $this->modules[$offset ?: count($this->modules)] = $module;
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