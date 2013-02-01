<?php

namespace Europa\Module;
use ArrayIterator;
use Europa\Config\Config;
use Europa\Exception\Exception;
use Europa\Di\ServiceContainerInterface;

class Manager implements ManagerInterface
{
    private $aliases = [];

    private $bootstrappedModules = [];

    private $container;

    private $isBootstrapped = false;

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

    public function alias($alias, $module)
    {
        $this->aliases[$alias] = $module;
        return $this;
    }

    public function bootstrap()
    {
        foreach ($this->modules as $module) {
            $module->bootstrap($this);
            
            $this->bootstrappedModules[] = $module;
        }

        $this->isBootstrapped = true;
        
        return $this;
    }

    public function isBootstrapped()
    {
        return $this->isBootstrapped;
    }

    public function isModuleBootstrapped($module)
    {
        return in_array($module, $this->bootstrappedModules, true);
    }

    public function offsetSet($offset, $module)
    {
        if (isset($this->aliases[$offset])) {
            $offset = $this->aliases[$offset];
        }

        if (!$module instanceof ModuleInterface) {
            Exception::toss('The module "%s" must be implement Europa\Module\ModuleInterface.', $offset);
        }

        $this->modules[$offset] = $module;

        return $this;
    }

    public function offsetGet($offset)
    {
        if (isset($this->modules[$offset])) {
            return $this->modules[$offset];
        }

        Exception::toss('The module "%s" does not exist.', $offset);
    }

    public function offsetExists($offset)
    {
        return isset($this->modules[$offset]);
    }

    public function offsetUnset($offset)
    {
        if (isset($this->modules[$offset])) {
            unset($this->modules[$offset]);
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