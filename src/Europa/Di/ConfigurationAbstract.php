<?php

namespace Europa\Di;
use ArrayIterator;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\FunctionReflector;
use IteratorAggregate;

abstract class ConfigurationAbstract implements IteratorAggregate
{
    const DOC_TAG_TRANSIENT = 'transient';

    private $methods;

    private $arguments = [];

    public function __invoke(ServiceContainerInterface $container)
    {
        foreach ($this as $name => $closure) {
            // Make sure arguments are merged.
            $arguments = isset($this->arguments[$name]) ? $this->arguments[$name] : [];

            // Rebind the closure so that it is bound to the container scope.
            $closure = $closure->bindTo($container);

            // Bind a proxy closure that will merge and call the configuration closure arguments at call time.
            $container->__set($name, function() use ($closure, $arguments) {
                $arguments = array_merge($arguments, func_get_args());
                return call_user_func_array($closure, $arguments);
            });

            // We check the doc block to see if there are any annotations.
            $closureReflector = new FunctionReflector($closure);

            // If the transient doc tag exists, then we mark it as transient.
            if ($closureReflector->getDocBlock()->hasTag(self::DOC_TAG_TRANSIENT)) {
                $container->transient($name);
            }
        }

        return $container;
    }

    public function setArguments($method)
    {
        $args = func_get_args();
        array_shift($args);
        return $this->setArgumentsArray($method, $args);
    }

    public function setArgumentsArray($method, array $arguments)
    {
        $this->arguments[$method] = $arguments;
        return $this;
    }

    public function getIterator()
    {
        $class = new ClassReflector($this);
        $this->methods = new ArrayIterator;

        foreach ($class->getMethods() as $method) {
            if ($method->isInherited() || $method->isMagic()) {
                continue;
            }

            $this->methods[$method->getName()] = $method->getClosure($this);
        }

        return $this->methods;
    }
}