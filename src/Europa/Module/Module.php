<?php

namespace Europa\Module;
use Europa\Config\Config;
use Europa\Config\ConfigInterface;
use Europa\Exception\Exception;
use Europa\Filter\UpperCamelCaseFilter;
use Europa\Router\Router;
use Europa\Fs\Locator;

/**
 * Handles the management of a single module.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Module
{
    /**
     * The module configuration.
     * 
     * @var array
     */
    private $config = [
        'config'             => 'configs/config.json',
        'routes'             => 'configs/routes.json',
        'src'                => 'src',
        'views'              => 'views',
        'requiredModules'    => [],
        'requiredExtensions' => [],
        'requiredClasses'    => [],
        'requiredFunctions'  => [],
        'bootstrapperPrefix' => 'Bootstrapper\\',
        'bootstrapperSuffix' => ''
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
            Exception::toss('The module path "%s" does not exist.', $path);
        }

        $this->name   = basename($this->path);
        $this->config = new Config($this->config, $config);
    }

    /**
     * Bootstraps the module.
     * 
     * @param ManagerInterface $manager The manager that is managing this module.
     * 
     * @return Module
     */
    public function __invoke(ManagerInterface $manager)
    {
        // Ensure all requirements are met.
        $this->validate($manager);

        // Bootstrap all dependency modules first.
        foreach ($this->config->requiredModules as $module) {
            $manager->offsetGet($module)->bootstrap($manager);
        }

        // The service container used by the manager and application components.
        // We update this to make changes to the application as a whole.
        $container = $manager->getServiceContainer();
        $this->applyConfig($container->config);
        $this->applyRoutes($container->router);
        $this->applyClassPaths($container->loaderLocator);
        $this->applyViewPaths($container->viewLocator);

        // If the bootstrapper class exists, invoke it.
        if (class_exists($bootstrapper = $this->getBootstrapperClassName(), true)) {
            (new $bootstrapper)->__invoke($container);
        }

        return $this;
    }

    /**
     * Ensures that the module requirements are met.
     * 
     * @param ManagerInterface $manager The manager that is managing this module.
     * 
     * @return void
     */
    private function validate(ManagerInterface $manager)
    {
        $this->validateModules($manager);
        $this->validateExtensions();
        $this->validateClasses();
        $this->validateFunctions();
    }

    /**
     * Ensures that all required modules exist in the manager.
     * 
     * @param ManagerInterface $manager The manager that is managing this module.
     * 
     * @return void
     */
    private function validateModules(ManagerInterface $manager)
    {
        foreach ($this->config->requiredModules as $module) {
            if (!$manager->offsetExists($module)) {
                Exception::toss('The module "%s" is required by the module "%s".', $module, $this->name);
            }
        }
    }

    /**
     * Ensures that all required extensions are loaded.
     * 
     * @return void
     */
    private function validateExtensions()
    {
        foreach ($this->config->requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                Exception::toss('The extension "%s" is required by the module "%s".', $extension, $this->name);
            }
        }
    }

    /**
     * Ensures that all required classes are available.
     * 
     * @return void
     */
    private function validateClasses()
    {
        foreach ($this->config->requiredClasses as $class) {
            if (!class_exists($class, true)) {
                Exception::toss('The class "%s" is required by the module "%s".', $class, $this->name);
            }
        }
    }

    /**
     * Ensures that all required functions are available.
     * 
     * @return void
     */
    private function validateFunctions()
    {
        foreach ($this->config->requiredFunctions as $function) {
            if (!function_exists($function)) {
                Exception::toss('The function "%s" is required by the module "%s".', $function, $this->name);
            }
        }
    }

    /**
     * Updates the application configuration to include a configuration object for the module. The module configuration can be access by using the same name as the module from the top level configuration.
     * 
     * @param ConfigInterface $config The config to modify.
     * 
     * @return void
     */
    private function applyConfig(ConfigInterface $config)
    {
        if ($options = realpath($this->path . '/' . $this->config->config)) {
            $config->offsetGet($this->name)->import($options);
        }
    }

    /**
     * Updates the application routes.
     * 
     * @param Router $router The router to modify.
     * 
     * @return void
     */
    private function applyRoutes(Router $router)
    {
        if ($options = realpath($this->path . '/' . $this->config->routes)) {
            $router->import($options);
        }
    }

    /**
     * Updates the application class paths.
     * 
     * @param Locator $locator The locator to modify.
     * 
     * @return void
     */
    private function applyClassPaths(Locator $locator)
    {
        $paths = new Locator($this->path);
        $paths->addPaths((array) $this->config->src);
        $locator->addPaths($paths);
    }

    /**
     * Updates the application view paths.
     * 
     * @param Locator $locator The locator to modify.
     * 
     * @return void
     */
    private function applyViewPaths(Locator $locator)
    {
        $paths = new Locator($this->path);
        $paths->addPaths((array) $this->config->views);
        $locator->addPaths($paths);
    }

    /**
     * Returns the class name for a bootstrapper class corresponding to this module.
     * 
     * @return string
     */
    private function getBootstrapperClassName()
    {
        return $this->config->bootstrapperPrefix
            . (new UpperCamelCaseFilter)->__invoke($this->name)
            . $this->config->bootstrapperSuffix;
    }
}