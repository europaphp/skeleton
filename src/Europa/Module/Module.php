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
    private $classConfig = [
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

    private $moduleConfig;

    private $name;

    private $path;

    private $invoked = false;

    public function __construct($path, $config = [])
    {
        if (!$this->path = realpath($path)) {
            Exception::toss('The module path "%s" does not exist.', $path);
        }

        $this->name        = basename($this->path);
        $this->classConfig = new Config($this->classConfig, $config);

        if ($this->classConfig->config instanceof ConfigInterface) {
            $this->moduleConfig = $this->classConfig->config;
        } elseif ($path = realpath($this->path . '/' . $this->classConfig->config)) {
            $this->moduleConfig = new Config($path);
        } else {
            $this->moduleConfig = new Config;
        }
    }

    public function __invoke(ManagerInterface $manager)
    {
        if ($this->invoked) {
            return $this;
        }

        $this->invoked = true;

        // Ensure all requirements are met.
        $this->validate($manager);

        // Bootstrap all dependency modules first.
        foreach ($this->classConfig->requiredModules as $module) {
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
            var_dump(__FILE__.':'.__LINE__, spl_object_hash($this), $bootstrapper);
            (new $bootstrapper)->__invoke($this, $manager);
            var_dump(__FILE__.':'.__LINE__, spl_object_hash($this));
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
        return $this->moduleConfig;
    }

    public function offsetSet($name, $value)
    {
        $this->moduleConfig->offsetSet($name, $value);
        return $this;
    }

    public function offsetGet($name)
    {
        return $this->moduleConfig->offsetGet($name);
    }

    public function offsetExists($name)
    {
        return $this->moduleConfig->offsetExists($name);
    }

    public function offsetUnset($name)
    {
        $this->moduleConfig->offsetUnset($name);
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
        foreach ($this->classConfig->requiredModules as $module) {
            if (!$manager->offsetExists($module)) {
                Exception::toss('The module "%s" is required by the module "%s".', $module, $this->name);
            }
        }
    }

    private function validateExtensions()
    {
        foreach ($this->classConfig->requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                Exception::toss('The extension "%s" is required by the module "%s".', $extension, $this->name);
            }
        }
    }

    private function validateClasses()
    {
        foreach ($this->classConfig->requiredClasses as $class) {
            if (!class_exists($class, true)) {
                Exception::toss('The class "%s" is required by the module "%s".', $class, $this->name);
            }
        }
    }

    private function validateFunctions()
    {
        foreach ($this->classConfig->requiredFunctions as $function) {
            if (!function_exists($function)) {
                Exception::toss('The function "%s" is required by the module "%s".', $function, $this->name);
            }
        }
    }

    private function applyRoutes(Router $router)
    {
        if ($options = realpath($this->path . '/' . $this->classConfig->routes)) {
            $router->import($options);
        }
    }

    private function applyClassPaths(Locator $locator)
    {
        $paths = new Locator($this->path);
        $paths->addPaths((array) $this->classConfig->srcPaths);
        $locator->addPaths($paths);
    }

    private function applyViewPaths(Locator $locator)
    {
        $paths = new Locator($this->path);
        $paths->addPaths((array) $this->classConfig->viewPaths, false);
        $locator->addPaths($paths);
    }

    private function getBootstrapperClassName()
    {
        return $this->classConfig->bootstrapperPrefix
            . (new UpperCamelCaseFilter)->__invoke($this->name)
            . $this->classConfig->bootstrapperSuffix;
    }
}