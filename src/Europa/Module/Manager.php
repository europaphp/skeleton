<?php

namespace Europa\Module;
use Europa\Config\Config;
use Europa\Di\ServiceContainerInterface;

class Manager implements ManagerInterface
{
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
            $module($this);
        }
        
        return $this;
    }

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

    public function offsetGet($offset)
    {
        if (isset($this->modules[$offset])) {
            return $this->modules[$offset];
        }

        Exception::toss('The module at offset "%s" does not exist.', $offset);
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