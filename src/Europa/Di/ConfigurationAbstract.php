<?php

namespace Europa\Di;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;

abstract class ConfigurationAbstract
{
    const DOC_TAG_TRANSIENT = 'transient';

    public function __invoke(ServiceContainerInterface $container)
    {
        $class = new ClassReflector($this);

        foreach ($class->getMethods() as $method) {
            if ($this->isValidMethod($method)) {
                $this->applyMethodToContainer($method, $container);
            }
        }

        return $container;
    }

    private function applyMethodToContainer(MethodReflector $method, ServiceContainerInterface $container)
    {
        $container->__set($method->getName(), $method->getClosure($this));
        $this->applyTransient($method, $container);
        $this->applyMethodDependencies($method, $container);
    }

    private function applyTransient(MethodReflector $method, ServiceContainerInterface $container)
    {
        if ($method->getDocBlock()->hasTag(self::DOC_TAG_TRANSIENT)) {
            $container->transient($name);
        }
    }

    private function applyMethodDependencies(MethodReflector $method, ServiceContainerInterface $container)
    {
        $dependencies = [];

        foreach ($method->getParameters() as $param) {
            $dependencies[] = $param->getName();
        }

        $container->depends($method->getName(), $dependencies);
    }

    private function isValidMethod(MethodReflector $method)
    {
        return $method->isPublic()
            && !$method->isMagic()
            && !$method->isInherited();
    }
}