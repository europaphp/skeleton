<?php

namespace Europa\Module;
use ArrayIterator;
use Europa\Config\Config;
use Europa\Exception\Exception;
use Europa\Di\ServiceContainerInterface;

class Manager implements ManagerInterface
{
    private $aliases = [];

    private $container;

    private $modules = [];

    public function __construct(ServiceContainerInterface $container)
    {
        $this->setServiceContainer($container);
    }

    public function setServiceContainer(ServiceContainerInterface $container)
    {
        $this->container = $container->mustProvide('Europa\Module\ManagerConfigurationInterface');
        return $this;
    }

    public function getServiceContainer()
    {
        return $this->container;
    }

    public function bootstrap()
    {
        foreach ($this->modules as $module) {
            if (!$module->bootstrapped()) {
                $module->bootstrap($this);
            }
        }
        
        return $this;
    }

    public function offsetSet($offset, $module)
    {
        if (!$module instanceof ModuleInterface) {
            Exception::toss('The module "%s" must be implement Europa\Module\ModuleInterface.', $offset);
        }

        $name = $module->name();

        if (isset($this->modules[$name])) {
            Exception::toss('Cannot add module "%s" because it already exists. This may be because another module you are adding is attempting to use the same name.', $name);
        }

        // For our purposes, we always use the specified module name as the main offset.
        $this->modules[$name] = $module;

        // If an offset is specified, we consider that to be an alias for the module.
        // The alias is simply a reference to the module itself. This allows us to
        // simply access it via the modules array or the aliases array without any
        // excess overhead.
        if ($offset) {
            $this->aliases[$offset] = $module;
        }

        return $this;
    }

    public function offsetGet($offset)
    {
        if (isset($this->modules[$offset])) {
            return $this->modules[$offset];
        }

        if (isset($this->aliases[$offset])) {
            return $this->aliases[$offset];
        }

        Exception::toss('The module "%s" does not exist.', $offset);
    }

    public function offsetExists($offset)
    {
        return isset($this->modules[$offset]) || isset($this->aliases[$offset]);
    }

    public function offsetUnset($offset)
    {
        if (isset($this->modules[$offset])) {
            unset($this->modules[$offset]);
        }

        if (isset($this->aliases[$offset])) {
            unset($this->aliases[$offset]);
        }

        return $this;
    }

    public function count()
    {
        return count($this->modules);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->modules);
    }
}