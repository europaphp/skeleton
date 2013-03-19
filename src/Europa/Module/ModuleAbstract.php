<?php

namespace Europa\Module;
use Europa\Config\Config;
use Europa\Di\DependencyInjectorInterface;
use Europa\Filter\ClassNameFilter;
use ReflectionClass;

abstract class ModuleAbstract implements ModuleInterface
{
    const BOOTSTRAPPER = 'Bootstrapper';

    const VERSION = '0.0.0';

    protected $config = [];

    protected $dependencies = [];

    protected $name;

    protected $namespace;

    protected $path = '../..';

    public function __construct($config = [])
    {
        $this->initNamespace();
        $this->initName();
        $this->initPath();
        $this->initConfig($config);
        $this->init();
    }

    public function init()
    {
        
    }

    public function bootstrap(DependencyInjectorInterface $injector)
    {
        $class = $this->namespace . '\\' . static::BOOTSTRAPPER;

        if (class_exists($class)) {
            if (!is_subclass_of($class, 'Europa\Module\Bootstrapper\BootstrapperInterface')) {
                throw new Exception\InvalidBootstrapperInstance($class);
            }

            $class = new $class($this, $injector);
            $class->bootstrap();
        }
    }

    public function ns()
    {
        return $this->namespace;
    }

    public function name()
    {
        return $this->name;
    }

    public function version()
    {
        return static::VERSION;
    }

    public function path()
    {
        return $this->path;
    }

    public function config()
    {
        return $this->config;
    }

    public function dependencies()
    {
        return $this->dependencies;
    }

    private function formatNameToNamespace()
    {
        $filter = new ClassNameFilter;
        return $filter($this->name);
    }

    private function initNamespace()
    {
        if (!$this->namespace) {
            $this->namespace = get_class($this);
        }
    }

    private function initName()
    {
        if (!$this->name) {
            $this->name = $this->namespace;
        }

        $this->name = strtolower($this->name);
        $this->name = str_replace(['\\', '_'], '/', $this->name);
    }

    private function initPath()
    {
        $path = (new ReflectionClass($this))->getFileName();
        $path = dirname($path);

        if ($this->path) {
            $path .= '/' . $this->path;
        }

        if (!$this->path = realpath($path)) {
            Exception\InvalidPath($this->name, $path);
        }
    }

    private function initConfig($config)
    {
        if (is_string($this->config)) {
            $this->config = $this->path . '/' . $this->config;
        }

        $this->config = new Config($this->config, $config);
    }
}