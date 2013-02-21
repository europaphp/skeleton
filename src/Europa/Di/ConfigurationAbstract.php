<?php

namespace Europa\Di;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;

abstract class ConfigurationAbstract implements ConfigurationInterface
{
    public function configure(ContainerInterface $container)
    {
        $class = new ClassReflector($this);

        foreach ($class->getMethods() as $method) {
            if ($this->isValidMethod($method)) {
                $container->set($method->getName(), $method->getClosure($this));
            }
        }

        return $container;
    }

    private function isValidMethod(MethodReflector $method)
    {
        return $method->isPublic()
            && !$method->isMagic()
            && !$method->isInherited();
    }
}