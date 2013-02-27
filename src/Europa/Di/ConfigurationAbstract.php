<?php

namespace Europa\Di;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;

abstract class ConfigurationAbstract implements ConfigurationInterface
{
    const DOC_TAG_ALIAS = 'alias';

    const DOC_TAG_RETURN = 'return';

    const DOC_TAG_TRANSIENT = 'transient';

    public function configure(ContainerInterface $container)
    {
        $class = new ClassReflector($this);

        foreach ($class->getMethods() as $method) {
            if ($this->isValidMethod($method)) {
                $this->applyAliases($container, $method);
                $this->applyTransient($container, $method);
                $this->applyTypes($container, $method);
                $container->set($method->getName(), $method->getClosure($this));
            }
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

        $container->setAliases($method->getName(), $aliases);
    }

    private function applyTransient(ContainerInterface $container, MethodReflector $method)
    {
        if ($method->getDocBlock()->hasTag(self::DOC_TAG_TRANSIENT)) {
            $container->setTransient($method->getName());
        }
    }

    private function applyTypes(ContainerInterface $container, MethodReflector $method)
    {
        if ($method->getDocBlock()->hasTag(self::DOC_TAG_RETURN)) {
            $container->setTypes($method->getName(), $method->getDocBlock()->getTag(self::DOC_TAG_RETURN)->getTypes());
        }
    }
}