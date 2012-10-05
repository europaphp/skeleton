<?php

namespace Europa\Module;
use ArrayIterator;
use Europa\Event\Manager as EventManager;
use LogicException;
use UnexpectedValueException;

class Manager implements ManagerInterface
{
    private $event;

    private $modules = [];

    private $path;

    public function __construct($path)
    {
        if (!$this->path = realpath($path)) {
            throw new UnexpectedValueException(sprintf('The path "%s" does not exist.', $path));
        }

        $this->event = new EventManager;
    }

    public function __get($name)
    {
        if (!isset($this->modules[$name])) {
            throw new LogicException(sprintf('The module "%s" does not exist.'));
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

        $this->modules[] = $module;

        return $this;
    }

    public function setEvent(EventManager $event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function bootstrap()
    {
        $this->event->trigger('modules.bootstrap.pre');

        foreach ($this->modules as $module) {
            $this->event->trigger('module.bootstrap.pre', [$this, $module]);
            $this->event->trigger('module.' . $module->name() . '.bootstrap.pre', [$this, $module]);
            $module->bootstrap();
            $this->event->trigger('module.' . $module->name() . '.bootstrap.post', [$this, $module]);
            $this->event->trigger('module.bootstrap.post', [$this, $module]);
        }

        $this->event->trigger('modules.bootstrap.post');

        return $this;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->modules);
    }
}