<?php

namespace Europa\Module;
use ArrayAccess;
use Europa\Config\Config;
use Europa\Config\ConfigInterface;
use Europa\Exception\Exception;
use Europa\Filter\UpperCamelCaseFilter;
use Europa\Router\Router;
use Europa\Fs\Locator;

class Module implements ArrayAccess
{
    private $config = [
        'config'             => 'configs/config.json',
        'routes'             => 'configs/routes.json',
        'srcPaths'           => 'src',
        'viewPaths'          => 'views',
        'requiredModules'    => [],
        'requiredExtensions' => [],
        'requiredClasses'    => [],
        'requiredFunctions'  => [],
        'bootstrapperPrefix' => 'Bootstrapper\\',
        'bootstrapperSuffix' => ''
    ];

    private $name;

    private $path;

    public function __construct($path, $config = [])
    {
        if (!$this->path = realpath($path)) {
            Exception::toss('The module path "%s" does not exist.', $path);
        }

        $this->name   = basename($this->path);
        $this->config = new Config($this->config, $config);

        if ($path = realpath($this->path . '/' . $this->config->config)) {
            $this->config->import($path);
        }
    }

    public function __invoke(ManagerInterface $manager)
    {
        // Ensure all requirements are met.
        $this->validate($manager);

        // Bootstrap all dependency modules first.
        foreach ($this->config->requiredModules as $module) {
            $manager->offsetGet($module)->__invoke($manager);
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

    public function __toString()
    {
        return $this->name();
    }

    public function name()
    {
        return $this->name;
    }

    public function path()
    {
        return $this->path;
    }

    public function config()
    {
        return $this->config;
    }

    public function offsetSet($name, $value)
    {
        $this->config->offsetSet($name, $value);
        return $this;
    }

    public function offsetGet($name)
    {
        return $this->config->offsetGet($name);
    }

    public function offsetExists($name)
    {
        return $this->config->offsetExists($name);
    }

    public function offsetUnset($name)
    {
        $this->config->offsetUnset($name);
        return $this;
    }

    private function validate(ManagerInterface $manager)
    {
        $this->validateModules($manager);
        $this->validateExtensions();
        $this->validateClasses();
        $this->validateFunctions();
    }

    private function validateModules(ManagerInterface $manager)
    {
        foreach ($this->config->requiredModules as $module) {
            if (!$manager->offsetExists($module)) {
                Exception::toss('The module "%s" is required by the module "%s".', $module, $this->name);
            }
        }
    }

    private function validateExtensions()
    {
        foreach ($this->config->requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                Exception::toss('The extension "%s" is required by the module "%s".', $extension, $this->name);
            }
        }
    }

    private function validateClasses()
    {
        foreach ($this->config->requiredClasses as $class) {
            if (!class_exists($class, true)) {
                Exception::toss('The class "%s" is required by the module "%s".', $class, $this->name);
            }
        }
    }

    private function validateFunctions()
    {
        foreach ($this->config->requiredFunctions as $function) {
            if (!function_exists($function)) {
                Exception::toss('The function "%s" is required by the module "%s".', $function, $this->name);
            }
        }
    }

    private function applyRoutes(Router $router)
    {
        if ($options = realpath($this->path . '/' . $this->config->routes)) {
            $router->import($options);
        }
    }

    private function applyClassPaths(Locator $locator)
    {
        $paths = new Locator($this->path);
        $paths->addPaths((array) $this->config->srcPaths);
        $locator->addPaths($paths);
    }

    private function applyViewPaths(Locator $locator)
    {
        $paths = new Locator($this->path);
        $paths->addPaths((array) $this->config->viewPaths, false);
        $locator->addPaths($paths);
    }

    private function getBootstrapperClassName()
    {
        return $this->config->bootstrapperPrefix
            . (new UpperCamelCaseFilter)->__invoke($this->name)
            . $this->config->bootstrapperSuffix;
    }
}