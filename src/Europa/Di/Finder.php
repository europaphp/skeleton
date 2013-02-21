<?php

namespace Europa\Di;
use Closure;
use Europa\Filter\ClassNameFilter;
use Europa\Reflection\ClassReflector;

class Finder implements FinderInterface
{
    private $filter;

    private $args = [];

    private $callbacks = [];

    public function __construct(callable $filter = null)
    {
        $this->filter = $filter ?: new ClassNameFilter;
    }

    public function get($name)
    {
        $class = call_user_func($this->filter, $name);

        if (!class_exists($class)) {
            Exception::toss('The class "%s" does not exist.', $class);
        }

        $args = [];

        foreach ($this->args as $instanceof => $instanceofArgs) {
            if ($this->is($class, $instanceof)) {
                $args = array_merge($args, $instanceofArgs());
            }
        }

        $class = new ClassReflector($class);
        $class = $class->newInstanceArgs($args);

        foreach ($this->callbacks as $instanceof => $callback) {
            if ($this->is($class, $instanceof)) {
                $callback($class);
            }
        }

        return $class;
    }

    public function has($name)
    {
        return class_exists(call_user_func($this->filter, $name));
    }

    public function provides($blueprint)
    {
        $reflector = new ReflectionClass($blueprint);

        foreach ($reflector->getMethods() as $method) {
            if (!isset($this->has[$method->getName()])) {
                return false;
            }
        }

        return true;
    }

    public function addArgs($instanceof, Closure $closure)
    {
        $this->args[$instanceof] = $closure;
        return $this;
    }

    public function addCallback($instanceof, Closure $closure)
    {
        $this->callbacks[$instanceof] = $closure;
        return $this;
    }

    private function is($class, $instanceof)
    {
        return $class === $instanceof || is_subclass_of($class, $instanceof);
    }
}