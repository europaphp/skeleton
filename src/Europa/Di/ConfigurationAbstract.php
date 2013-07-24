<?php

namespace Europa\Di;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;

abstract class ConfigurationAbstract
{
    const DOC_TAG_ALIAS = 'alias';

    const DOC_TAG_RETURN = 'return';

    const DOC_TAG_TRANSIENT = 'transient';

    const METHOD_INIT = 'init';

    public function __invoke(ContainerInterface $container)
    {
        $class = new ClassReflector($this);

        foreach ($class->getMethods() as $method) {
            if ($this->isValidMethod($method)) {
                $this->applyAliases($container, $method);
                $this->applyDependencies($container, $method);
                $this->applyTransient($container, $method);
                $this->applyTypes($container, $method);
                $container->register($method->getName(), $method->getClosure($this));
            }
        }

        if (method_exists($this, self::METHOD_INIT)) {
            $this->{self::METHOD_INIT}($container);
        }

        return $this;
    }

    private function isValidMethod(MethodReflector $method)
    {
        return $method->isPublic()
            && !$method->isMagic()
            && !$method->isInherited();
    }

    private function applyAliases(ContainerInterface $container, MethodReflector $method)
    {
        $docblock = $method->getDocBlock();
        $aliases  = [];

        if ($docblock->hasTag(self::DOC_TAG_ALIAS)) {
            foreach ($docblock->getTag(self::DOC_TAG_ALIAS) as $tag) {
                $aliases[] = $tag->value();
            }
        }

        if ($aliases) {
            $container->alias($method->getName(), $aliases);
        }
    }

    private function applyDependencies(ContainerInterface $container, MethodReflector $method)
    {
        $dependencies = [];

        foreach ($method->getParameters() as $param) {
            $dependencies[] = $param->getName();
        }

        if ($dependencies) {
            $container->depends($method->getName(), $dependencies);
        }
    }

    private function applyTransient(ContainerInterface $container, MethodReflector $method)
    {
        if ($method->getDocBlock()->hasTag(self::DOC_TAG_TRANSIENT)) {
            $container->template($method->getName());
        }
    }

    private function applyTypes(ContainerInterface $container, MethodReflector $method)
    {
        if ($method->getDocBlock()->hasTag(self::DOC_TAG_RETURN)) {
            $container->constrain($method->getName(), $method->getDocBlock()->getTag(self::DOC_TAG_RETURN)->getTypes());
        }
    }
}