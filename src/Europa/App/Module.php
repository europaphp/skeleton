<?php

namespace Europa\App;
use Europa\Config\Config;
use Europa\Exception\Exception;
use Europa\Filter\UpperCamelCaseFilter;
use Europa\Fs\Locator;

class Module implements ModuleInterface
{
    private $config = [
        'ns.bootstrapper' => 'Bootstrapper',
        'files.config'    => 'configs/config.json',
        'files.routes'    => 'configs/routes.json',
        'paths.classes'   => ['classes'],
        'paths.views'     => ['views']
    ];

    private $name;

    private $path;

    public function __construct($path, $config = [])
    {
        if (!$this->path = realpath($path)) {
            Exception::toss('The path "%s" does not exist.', $path);
        }

        $this->name    = basename($this->path);
        $this->config  = new Config($this->config, $config);
        $this->locator = new Locator;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getConfig()
    {
        return $config = realpath($this->path . '/' . $this->config->files->config) ?: [];
    }

    public function getRoutes()
    {
        return $routes = realpath($this->path . '/' . $this->config->files->routes) ?: [];
    }

    public function getClassPaths()
    {
        $locator = new Locator($this->path);
        $locator->addPaths($this->config->paths->classes);
        return $locator;
    }

    public function getViewPaths()
    {
        $locator = new Locator($this->path);
        $locator->addPaths($this->config->paths->views);
        return $locator;
    }

    public function getBootstrapper()
    {
        $class = (new UpperCamelCaseFilter)->__invoke($this->name);
        $class = $this->config->ns->bootstrapper . '\\' . $class;

        if (class_exists($class)) {
            return new $class;
        }
    }
}