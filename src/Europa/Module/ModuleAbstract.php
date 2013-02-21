<?php

namespace Europa\Module;
use Europa\Config\Config;
use Europa\Di\ContainerInterface;
use Europa\Exception\Exception;
use Europa\Filter\ClassNameFilter;
use ReflectionClass;

abstract class ModuleAbstract implements ModuleInterface
{
    const BOOTSTRAPPER = 'Bootstrapper';

    const VERSION = '0.0.0';

    protected $config = [];

    protected $dependencies = [];

    protected $path = '../..';

    public function __construct($config = [])
    {
        $this->initPath();
        $this->initConfig();
        $this->init();
    }

    public function init()
    {
        
    }

    public function bootstrap(ContainerInterface $container)
    {
        $class = $this->formatNameToNamespace() . '\\' . self::BOOTSTRAPPER;

        if (class_exists($class)) {
            $class = new $class($this, $container);
            $class->bootstrap();
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getName()
    {
        $name = get_class($this);
        $name = strtolower($name);
        $name = str_replace(['\\', '_'], '/', $name);
        return $name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getVersion()
    {
        return static::VERSION;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    private function formatNameToNamespace()
    {
        $filter = new ClassNameFilter;
        return $filter($this->getName());
    }

    private function initPath()
    {
        $path = (new ReflectionClass($this))->getFileName();
        $path = dirname($path);

        if ($this->path) {
            $path .= '/' . $this->path;
        }

        if (!$this->path = realpath($path)) {
            Exception::toss('The module "%s" specified and invalid path "%s".', $this->getName(), $path);
        }
    }

    private function initConfig()
    {
        if (is_string($this->config)) {
            $this->config = $this->path . '/' . $this->config;
        }

        $this->config = new Config($this->config, $config);
    }
}