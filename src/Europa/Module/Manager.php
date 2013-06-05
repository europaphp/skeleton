<?php

namespace Europa\Module;
use ArrayIterator;
use Europa\Exception\Exception;
use Europa\Version\SemVer;
use ReflectionExtension;

class Manager implements ManagerInterface
{
    const VALID_NAME_REGEX = '/[a-z][a-z0-9\-]*\/[a-z][a-z0-9\-]*/';

    private $bootstrapped = [];

    private $container;

    private $modules = [];

    public function __construct(callable $container)
    {
        $this->container = $container;
    }

    public function bootstrap()
    {
        foreach ($this->modules as $module) {
            if (!in_array($module->name(), $this->bootstrapped)) {
                $this->validate($module);
                $this->bootstrapDependencies($module);
                $module->bootstrap($this->container);
                $this->bootstrapped[] = $module->name();
            }
        }

        return $this;
    }

    public function add(ModuleInterface $module)
    {
        $name = $module->name();

        if (isset($this->modules[$name])) {
            Exception::toss('Cannot add module "%s" because it already exists. This may be because another module you are adding is attempting to use the same name.', $name);
        }

        if (!preg_match(self::VALID_NAME_REGEX, $name)) {
            Exception::toss('Modules names are required to be in the format "vendor-name/module-name". The name must be compliant with https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md.');
        }

        $this->modules[$name] = $module;

        return $this;
    }

    public function get($name)
    {
        if (isset($this->modules[$name])) {
            return $this->modules[$name];
        }

        Exception::toss('The module "%s" does not exist.', $name);
    }

    public function has($name)
    {
        return isset($this->modules[$name]);
    }

    public function count()
    {
        return count($this->modules);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->modules);
    }

    private function validate(ModuleInterface $module)
    {
        foreach ($module->dependencies() as $name => $version) {
            if (!$this->has($name)) {
                Exception::toss(
                    'The module "%s" is required by the module "%s".',
                    $name,
                    $module->name()
                );
            }

            $version = new SemVer($version);

            if (!$version->is($this->get($name)->version())) {
                Exception::toss(
                    'The module "%s", currently at version "%s", is required to be at version "%s" by the module "%s".',
                    $name,
                    $this->get($name)->version(),
                    $version,
                    $module->name()
                );
            }
        }
    }

    private function bootstrapDependencies(ModuleInterface $module)
    {
        foreach ($module->dependencies() as $name => $version) {
            if (!in_array($name, $this->bootstrapped)) {
                $this->get($name)->bootstrap($this->container);
            }
        }
    }
}