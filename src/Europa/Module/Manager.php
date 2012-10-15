<?php

namespace Europa\Module;
use ArrayIterator;
use Europa\Fs\Loader\ClassLoader;
use Europa\Fs\Locator\LocatorArray;
use LogicException;
use UnexpectedValueException;

class Manager implements ManagerInterface
{
    private $modules = [];

    private $path;

    public function __construct($path)
    {
        if (!$this->path = realpath($path)) {
            throw new UnexpectedValueException(sprintf('The path "%s" does not exist.', $path));
        }

        $this->classLoader = new ClassLoader;
        $this->langLocator = new LocatorArray;
        $this->viewLocator = new LocatorArray;
    }

    public function __get($name)
    {
        if (!isset($this->modules[$name])) {
            throw new LogicException(sprintf('The module "%s" does not exist.', $name));
        }
        
        return $this->modules[$name];
    }

    public function __isset($name)
    {
        return isset($this->modules[$name]);
    }

    public function path()
    {
        return $this->path;
    }

    public function register($module)
    {
        if (!$module instanceof ModuleInterface) {
            $module = new Module($this->path . '/' . $module);
        }

        $this->modules[$module->name()] = $module;

        return $this;
    }

    public function bootstrap()
    {
        $this->classLoader->register();

        foreach ($this->modules as $module) {
            $this->classLoader->getLocator()->add($module->getClassLocator());
            $this->langLocator->add($module->getLangLocator());
            $this->viewLocator->add($module->getViewLocator());
            $module->bootstrap();
        }

        return $this;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->modules);
    }
}