<?php

namespace Europa\Di;
use Europa\Reflection\ClassReflector;

abstract class ConfigurationAbstract implements ConfigurationInterface
{
    public function configure(ContainerInterface $container)
    {
        $class = new ClassReflector($this);

        foreach ($class->getMethods() as $method) {
            if ($method->isInherited()) {
                continue;
            }

            $name = $method->getName();
            
            $container->__set($name, function() use ($name) {
                return call_user_func_array([$this, $name], func_get_args());
            });
        }
    }
}