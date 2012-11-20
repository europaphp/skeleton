<?php

namespace Europa\App;
use Europa\Config\Config;
use Europa\Exception\Exception;
use Europa\Filter\UpperCamelCaseFilter;
use Europa\Fs\Locator;

/**
 * Handles the management of a single module.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Module implements ModuleInterface
{
    /**
     * The module configuration.
     * 
     * @var array
     */
    private $config = [
        'bootstrapperNs' => 'Bootstrapper',
        'config'         => 'configs/config.json',
        'routes'         => 'configs/routes.json',
        'src'            => ['src'],
        'views'          => ['views']
    ];

    /**
     * The module name.
     * 
     * @var string
     */
    private $name;

    /**
     * The module installation path.
     * 
     * @var string
     */
    private $path;

    /**
     * List of required modules.
     * 
     * @var array
     */
    private $required = [];

    /**
     * Sets up a new module.
     * 
     * @param string $path   The path to the module installation directory.
     * @param array  $config The module component configuration.
     * 
     * @return Module
     */
    public function __construct($path, $config = [])
    {
        if (!$this->path = realpath($path)) {
            Exception::toss('The path "%s" does not exist.', $path);
        }

        $this->name    = basename($this->path);
        $this->config  = new Config($this->config, $config);
        $this->locator = new Locator;
    }

    /**
     * Requires the specified module.
     * 
     * @param string $module
     */
    public function requires($module)
    {
        $this->required[] = $module;
        return $this;
    }

    /**
     * Returns the names of all dependent modules.
     * 
     * @return array
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Returns the name of the module.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the module configuration. Can be a path to a configuration file or array.
     * 
     * @return mixed
     */
    public function getConfig()
    {
        return realpath($this->path . '/' . $this->config->config) ?: [];
    }

    /**
     * Returns the module routes. Can be a path to the routes file or array.
     * 
     * @return mixed
     */
    public function getRoutes()
    {
        return realpath($this->path . '/' . $this->config->routes) ?: [];
    }

    /**
     * Returns the autoloadable class paths. Can be anything that is traversable.
     * 
     * @return mixed
     */
    public function getClassPaths()
    {
        $locator = new Locator($this->path);
        $locator->addPaths($this->config->src);
        return $locator;
    }

    /**
     * Returns the base view paths. Can be anything that is traversable.
     * 
     * @return mixed
     */
    public function getViewPaths()
    {
        $locator = new Locator($this->path);
        $locator->addPaths($this->config->views);
        return $locator;
    }

    /**
     * Returns the bootstrapper that should boot the module. This can be anything that is callable.
     * 
     * @return callable
     */
    public function getBootstrapper()
    {
        $class = (new UpperCamelCaseFilter)->__invoke($this->name);
        $class = $this->config->bootstrapperNs . '\\' . $class;

        if (class_exists($class)) {
            return new $class;
        }
    }
}