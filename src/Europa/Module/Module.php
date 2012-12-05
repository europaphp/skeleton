<?php

namespace Europa\Module;
use ArrayAccess;
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
class Module implements ArrayAccess
{
    /**
     * The module configuration.
     * 
     * @var array
     */
    private $classConfig = [
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
     * The module config.
     * 
     * @var ConfigInterface
     */
    private $moduleConfig;

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

        // The name of the directory the module is in.
        $this->name = basename($this->path);

        // The class configuration.
        $this->classConfig = new Config($this->classConfig, $config);

        // The specified module config can either be a path or a config object.
        if ($this->classConfig->config instanceof ConfigInterface) {
            $this->moduleConfig = $this->classConfig->config;
        } elseif ($path = realpath($this->path . '/' . $this->classConfig->config)) {
            $this->moduleConfig = new Config($path);
        }
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
        foreach ($this->classConfig->requiredModules as $module) {
            $manager->offsetGet($module)->bootstrap($manager);
        }

        // The service container used by the manager and application components.
        // We update this to make changes to the application as a whole.
        $container = $manager->getServiceContainer();
        $this->applyRoutes($container->router);
        $this->applyClassPaths($container->loaderLocator);
        $this->applyViewPaths($container->viewLocator);

        // If the bootstrapper class exists, invoke it.
        if (class_exists($bootstrapper = $this->getBootstrapperClassName(), true)) {
            (new $bootstrapper)->__invoke($this, $manager);
        }

        return $this;
    }

    /**
     * Allows runtime modification of module config.
     * 
     * @param string $name  The option name.
     * @param mixed  $value The option value.
     */
    public function offsetSet($name, $value)
    {
        Exception::toss('Module configuration cannot be set after instantiation.');
    }

    /**
     * Returns a module config option.
     * 
     * @param string $name The option name.
     * 
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->moduleConfig->offsetGet($name);
    }

    /**
     * Returns whether or not a module config option is set.
     * 
     * @param string $name The option name.
     * 
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->moduleConfig->offsetExists($name);
    }

    /**
     * Removes a module config option.
     * 
     * @param string $name The option name.
     * 
     * @return mixed
     */
    public function offsetUnset($name)
    {
        $this->moduleConfig->offsetUnset($name);
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
        foreach ($this->classConfig->requiredModules as $module) {
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
        foreach ($this->classConfig->requiredExtensions as $extension) {
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
        foreach ($this->classConfig->requiredClasses as $class) {
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
        foreach ($this->classConfig->requiredFunctions as $function) {
            if (!function_exists($function)) {
                Exception::toss('The function "%s" is required by the module "%s".', $function, $this->name);
            }
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
        if ($options = realpath($this->path . '/' . $this->classConfig->routes)) {
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
        $paths->addPaths((array) $this->classConfig->src);
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
        $paths->addPaths((array) $this->classConfig->views);
        $locator->addPaths($paths);
    }

    /**
     * Returns the class name for a bootstrapper class corresponding to this module.
     * 
     * @return string
     */
    private function getBootstrapperClassName()
    {
        return $this->classConfig->bootstrapperPrefix
            . (new UpperCamelCaseFilter)->__invoke($this->name)
            . $this->classConfig->bootstrapperSuffix;
    }
}