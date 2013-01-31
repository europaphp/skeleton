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
        'configs'            => ['configs/config.yml'],
        'routes'             => ['configs/routes.yml'],
        'srcPaths'           => ['src'],
        'viewPaths'          => ['views'],
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
        $this->initPathAndName($path);
        $this->initConfig($config);
    }

    public function __invoke(ManagerInterface $manager)
    {
        $this->applyConfigs($manager);
        $this->validateManager($manager);
        $this->bootstrapDependencies($manager);
        $this->applyRoutes($manager);
        $this->applyClassPaths($manager);
        $this->applyViewPaths($manager);
        $this->bootstrap($manager);
        
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

    private function validateManager(ManagerInterface $manager)
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

    private function bootstrapDependencies(ManagerInterface $manager)
    {
        foreach ($this->config->requiredModules as $module) {
            $module = $manager->offsetGet($module);

            if (!$manager->isModuleBootstrapped($module)) {
                $module($manager);
            }
        }
    }

    private function applyConfigs(ManagerInterface $manager)
    {
        $manager->getServiceContainer()->config->modules[$this->name] = $this->config;

        foreach ($this->config->configs as $k => $config) {
            if ($path = realpath($this->path . '/' . $config)) {
                $this->config->import($path);
            }
        }
    }

    private function applyRoutes(ManagerInterface $manager)
    {
        $router = $manager->getServiceContainer()->router;

        foreach ($this->config->routes as $routes) {
            if ($options = realpath($this->path . '/' . $routes)) {
                $router->import($options);
            }
        }
    }

    private function applyClassPaths(ManagerInterface $manager)
    {
        $paths = new Locator($this->path);
        $paths->addPaths($this->config->srcPaths);
        $manager->getServiceContainer()->loaderLocator->addPaths($paths);
    }

    private function applyViewPaths(ManagerInterface $manager)
    {
        $paths = new Locator($this->path);
        $paths->addPaths($this->config->viewPaths, false);
        $manager->getServiceContainer()->viewLocator->addPaths($paths);
    }

    private function bootstrap(ManagerInterface $manager)
    {
        if (class_exists($bootstrapper = $this->getBootstrapperClassName(), true)) {
            (new $bootstrapper)->__invoke($this, $manager);
        }
    }

    private function getBootstrapperClassName()
    {
        return $this->config->bootstrapperPrefix
            . (new UpperCamelCaseFilter)->__invoke($this->name)
            . $this->config->bootstrapperSuffix;
    }

    private function initPathAndName($path)
    {
        if (!$this->path = realpath($path)) {
            Exception::toss('The module path "%s" does not exist.', $path);
        }

        $this->name = basename($this->path);
    }

    private function initConfig($config)
    {
        $this->config = new Config($this->config, $config);
    }
}