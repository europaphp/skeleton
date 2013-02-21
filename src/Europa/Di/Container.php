<?php

namespace Europa\Di;
use Closure;
use Europa\Exception\Exception;
use Europa\Reflection\FunctionReflector;
use Europa\Reflection\ReflectorInterface;
use ReflectionClass;

class Container implements ContainerInterface
{
    const DOC_TAG_ALIAS = 'alias';

    const DOC_TAG_BIND = 'bind';

    const DOC_TAG_DEPENDENCY = 'dependency';

    const DOC_TAG_PRIVATE = 'private';

    const DOC_TAG_TRANSIENT = 'transient';

    private $aliases = [];

    private $cache = [];

    private $dependencies = [];

    private $private = [];

    private $services = [];

    private $transient = [];

    public function __clone()
    {
        $this->cache = [];
    }

    public function set($name, Closure $service)
    {
        $name = $this->resolveAlias($name);

        $this->uncache($name);

        $this->services[$name] = $service->bindTo($this);

        $this->configureService($name, $this->services[$name]);

        return $this;
    }

    public function get($name)
    {
        $name = $this->resolveAlias($name);

        if (isset($this->private[$name])) {
            Exception::toss('The service "%s" is not publicly accessible.', $name);
        }

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (isset($this->services[$name])) {
            $service = $this->services[$name];
        } else {
            Exception::toss('The service "%s" does not exist.', $name);
        }

        $service = call_user_func_array($service, $this->resolveDependenciesFor($name));

        if (isset($this->transient[$name])) {
            return $service;
        }

        return $this->cache[$name] = $service;
    }

    public function has($name)
    {
        return isset($this->services[$this->resolveAlias($name)]);
    }

    public function remove($name)
    {
        $name = $this->resolveAlias($name);

        $this->uncache($name);

        if (isset($this->services[$name])) {
            unset($this->services[$name]);
        }

        return $this;
    }

    public function setAliases($name, array $aliases)
    {
        foreach ($aliases as $alias) {
            $this->aliases[$alias] = $name;
        }

        return $this;
    }

    public function setDependencies($name, array $dependencies)
    {
        $this->dependencies[$name] = $dependencies;
        return $this;
    }

    public function setPrivate($name)
    {
        $this->private[] = $name;
        return $this;
    }

    public function setTransient($name)
    {
        $this->transient[] = $name;

        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }

        return $this;
    }

    public function provides($blueprint)
    {
        $reflector = new ReflectionClass($blueprint);

        foreach ($reflector->getMethods() as $method) {
            if (!isset($this->services[$method->getName()])) {
                return false;
            }
        }

        return true;
    }

    private function uncache($name)
    {
        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }

        foreach ($this->dependencies as $service => $dependencies) {
            if (isset($this->cache[$service]) && in_array($name, $dependencies)) {
                unset($this->cache[$service]);
            }
        }
    }

    private function resolveAlias($name)
    {
        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        }

        return $name;
    }

    private function resolveDependenciesFor($name)
    {
        $dependencies = [];

        if (isset($this->dependencies[$name])) {
            foreach ($this->dependencies[$name] as $dependency) {
                if ($this->has($dependency)) {
                    $dependencies[] = $this->get($dependency);
                } else {
                    Exception::toss('The service "%s" requires that the dependency "%s" is defined.', $name, $dependency);
                }
            }
        }

        return $dependencies;
    }

    private function configureService($name, Closure $closure)
    {
        $closure = new FunctionReflector($closure);

        $this->applyAliases($name, $closure);
        $this->applyDependencies($name, $closure);
        $this->applyPrivate($name, $closure);
        $this->applyTransient($name, $closure);
    }

    private function applyAliases($name, ReflectorInterface $closure)
    {
        $docblock = $closure->getDocBlock();
        $aliases  = [];

        if ($docblock->hasTag(self::DOC_TAG_ALIAS)) {
            foreach ($docblock->getTag(self::DOC_TAG_ALIAS) as $tag) {
                $aliases[] = $tag->value();
            }
        }

        $this->setAliases($name, $aliases);
    }

    private function applyDependencies($name, ReflectorInterface $closure)
    {
        $docblock     = $closure->getDocBlock();
        $dependencies = [];

        foreach ($closure->getParameters() as $index => $param) {
            $dependencies[$index] = $param->getName();
        }

        if ($docblock->hasTag(self::DOC_TAG_DEPENDENCY)) {
            foreach ($docblock->getTag(self::DOC_TAG_DEPENDENCY) as $index => $tag) {
                $dependencies[$index] = $tag->value();
            }
        }

        $this->setDependencies($name, $dependencies);
    }

    private function applyPrivate($name, ReflectorInterface $closure)
    {
        if ($closure->getDocBlock()->hasTag(self::DOC_TAG_PRIVATE)) {
            $this->setPrivate($name);
        }
    }

    private function applyTransient($name, ReflectorInterface $closure)
    {
        if ($closure->getDocBlock()->hasTag(self::DOC_TAG_TRANSIENT)) {
            $this->setTransient($name);
        }
    }
}