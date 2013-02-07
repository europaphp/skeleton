<?php

namespace Europa\Module;
use ArrayAccess;
use Europa\Config\Config;
use Europa\Config\ConfigInterface;
use Europa\Exception\Exception;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Locator;
use Europa\Router\Router;
use Europa\Version\SemVer;
use ReflectionExtension;

class Module implements ArrayAccess, ModuleInterface
{
    private $bootstrapped = false;

    private $config = [
        'config-files'       => ['configs/config.yml'],
        'route-files'        => ['configs/routes.yml'],
        'name'               => null,
        'version'            => '0',
        'autoload-paths'     => ['src'],
        'view-paths'         => ['views'],
        'requiredModules'    => [],
        'requiredExtensions' => [],
        'requiredClasses'    => [],
        'requiredFunctions'  => [],
        'bootstrapperPrefix' => '',
        'bootstrapperSuffix' => ''
    ];

    private $path;

    public function __construct($path, $config = [])
    {
        $this->initPath($path);
        $this->initConfig($config);
        $this->validateConfig();
    }

    public function __toString()
    {
        return $this->name();
    }

    public function bootstrap(ManagerInterface $manager)
    {
        $this->bootstrapped = true;

        $this->validateManager($manager);
        $this->bootstrapDependencies($manager);
        $this->invokeBootstrapper($manager);
        
        return $this;
    }

    public function bootstrapped()
    {
        return $this->bootstrapped;
    }

    public function autoloadPaths()
    {
        $paths = [];

        foreach ($this->config['autoload-paths'] as $path) {
            $paths[] = $this->path() . '/' . $path;
        }

        return $paths;
    }

    public function config()
    {
        return $this->config;
    }

    public function name()
    {
        return $this->config->name;
    }

    public function path()
    {
        return $this->path;
    }

    public function routes()
    {
        $routes = new Config;

        foreach ($this->config['route-files'] as $file) {
            if (realpath($file = $this->path . '/' . $file)) {
                $routes->import($file);
            }
        }

        return $routes;
    }

    public function viewPaths()
    {
        $paths = [];

        foreach ($this->config['view-paths'] as $path) {
            $paths[] = $this->path() . '/' . $path;
        }

        return $paths;
    }

    public function version()
    {
        return $this->config->version;
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
        foreach ($this->config->requiredModules as $module => $version) {
            if (!$manager->offsetExists($module)) {
                Exception::toss(
                    'The module "%s" is required by the module "%s".',
                    $module,
                    $this->name()
                );
            }

            $version = new SemVer($version);

            if (!$version->is($manager->offsetGet($module)->version())) {
                Exception::toss(
                    'The module "%s", currently at version "%s", is required to be at version "%s" by the module "%s".',
                    $module,
                    $manager->offsetGet($module)->version(),
                    $version,
                    $this->name()
                );
            }
        }
    }

    private function validateExtensions()
    {
        foreach ($this->config->requiredExtensions as $extension => $version) {
            if (!extension_loaded($extension)) {
                Exception::toss(
                    'The extension "%s" is not loaded and is required by the module "%s".',
                    $extension,
                    $this->name()
                );
            }

            $extension = new ReflectionExtension($extension);
            $version   = new SemVer($version);

            if (!$version->is($extension->getVersion())) {
                Exception::toss(
                    'The extension "%s", curretly at version "%s", is required to be at version "%s" by the module "%s".',
                    $extension->getName(),
                    $extension->getVersion(),
                    $version,
                    $this->name()
                );
            }
        }
    }

    private function validateClasses()
    {
        foreach ($this->config->requiredClasses as $class) {
            if (!class_exists($class, true)) {
                Exception::toss('The class "%s" is required by the module "%s".', $class, $this->name());
            }
        }
    }

    private function validateFunctions()
    {
        foreach ($this->config->requiredFunctions as $function) {
            if (!function_exists($function)) {
                Exception::toss('The function "%s" is required by the module "%s".', $function, $this->name());
            }
        }
    }

    private function bootstrapDependencies(ManagerInterface $manager)
    {
        foreach ($this->config->requiredModules as $module => $version) {
            $module = $manager->offsetGet($module);

            if (!$module->bootstrapped()) {
                $module->bootstrap($manager);
            }
        }
    }

    private function invokeBootstrapper(ManagerInterface $manager)
    {
        if (class_exists($bootstrapper = $this->getBootstrapperClassName())) {
            (new $bootstrapper)->__invoke($this, $manager);
        }
    }

    private function getBootstrapperClassName()
    {
        return $this->config->bootstrapperPrefix
            . (new ClassNameFilter)->__invoke($this->name())
            . $this->config->bootstrapperSuffix;
    }

    private function initPath($path)
    {
        if (!$this->path = realpath($path)) {
            Exception::toss('The module path "%s" does not exist.', $path);
        }
    }

    private function initConfig($config)
    {
        $this->config = new Config($this->config, $config);

        foreach ($this->config['config-files'] as $file) {
            if ($config = realpath($this->path . '/' . $file)) {
                $this->config->import($config);
            }
        }
    }

    private function validateConfig()
    {
        $name = $this->config->name;

        if (!$name) {
            Exception::toss('You must specify a "name" in your module configuration.');
        }

        $parts = explode('/', $name);

        if (count($parts) !== 2) {
            Exception::toss('Module name must be in the format of "vendor/module-name".');
        }

        if (!$parts[0]) {
            Exception::toss('The vendor part of the module name "%s" cannot be empty.', $name);
        }

        if (!$parts[1]) {
            Exception::toss('The module-name part of the module name "%s" cannot be empty.', $name);
        }
    }
}