<?php

namespace Europa\Reflection;
use Europa\Exception\Exception;
use ReflectionMethod;

class MethodReflector extends ReflectionMethod implements ParameterAwareInterface, ReflectorInterface
{
    use ParameterAwareTrait;

    private $classname;

    private $docString;

    public function __construct($class, $name)
    {
        parent::__construct($class, $name);
        $this->classname = is_object($class) ? get_class($class) : $class;
    }

    public function __toString()
    {
        return $this->classname . $this->getType() . $this->getName();
    }

    public function getClass()
    {
        return new ClassReflector($this->classname);
    }

    public function getType()
    {
        return $this->isStatic() ? '::' : '->';
    }

    public function getVisibility()
    {
        if ($this->isPrivate()) {
            return 'private';
        }

        if ($this->isProtected()) {
            return 'protected';
        }

        return 'public';
    }

    public function isInherited()
    {
        return $this->class !== $this->classname;
    }

    public function isMagic()
    {
        return strpos($this->getName(), '__') === 0;
    }

    public function invokeArgs($instance, array $args = array())
    {
        if (func_num_args() === 2 && $this->getNumberOfParameters() > 0) {
            return parent::invokeArgs($instance, $this->mergeNamedArgs($args));
        }

        return $this->invoke($instance);
    }

    public function getDocBlock()
    {
        return new DocBlock($this->getDocComment());
    }

    public function getDocComment()
    {
        if ($this->docString) {
            return $this->docString;
        }

        if ($docblock = parent::getDocComment()) {
            $this->docString = $docblock;
            return $this->docString;
        }

        $name = $this->getName();

        foreach ($this->getDeclaringClass()->getTraits() as $trait) {
            if (!$trait->hasMethod($name)) {
                continue;
            }

            if ($this->docString = $trait->getMethod($name)->getDocComment()) {
                return $this->docString;
            }
        }

        foreach ($this->getDeclaringClass()->getInterfaces() as $iface) {
            if (!$iface->hasMethod($name)) {
                continue;
            }

            if ($this->docString = $iface->getMethod($name)->getDocComment()) {
                return $this->docString;
            }
        }

        return $this->docString;
    }
}