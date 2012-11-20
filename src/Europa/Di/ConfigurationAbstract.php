<?php

namespace Europa\Di;
use ArrayIterator;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\FunctionReflector;
use IteratorAggregate;

/**
 * Allows one to specify a class of methods that return dependencies.
 * 
 * @category Di
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
abstract class ConfigurationAbstract implements IteratorAggregate
{
    /**
     * The name of the transient doc tag.
     * 
     * @var string
     */
    const DOC_TAG_TRANSIENT = 'transient';

    /**
     * The methods available.
     * 
     * @param array
     */
    private $methods;

    /**
     * The argument sets to call a service configuratio nmethod with.
     * 
     * @param array
     */
    private $arguments = [];

    /**
     * Configures the specified service container.
     * 
     * @param ServiceContainerInterface $container The container to configure.
     * 
     * @return ServiceContainerInterface
     */
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

    /**
     * Sets arguments like `setArgumentsArray`, however, this allows you to specify them as arguments rather than in
     * an array.
     * 
     * @param string $method The method to call.
     * 
     * @return ConfigurationAbstract
     */
    public function setArguments($method)
    {
        $args = func_get_args();
        array_shift($args);
        return $this->setArgumentsArray($method, $args);
    }

    /**
     * Sets the arguments that should be passed to the specified method. If arguments are given at call time, they will
     * be merged with the arguments specified here, but will take precedence over these.
     * 
     * @param string $method    The method to call.
     * @param array  $arguments The arguments to pass to the specified method.
     * 
     * @return ConfigurationAbstract
     */
    public function setArgumentsArray($method, array $arguments)
    {
        $this->arguments[$method] = $arguments;
        return $this;
    }

    /**
     * Returns all the methods available in the configuration.
     * 
     * @return ArrayIterator
     */
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