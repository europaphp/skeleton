<?php

namespace Europa\Reflection;
use Europa\Exception\Exception;
use ReflectionMethod;

class MethodReflector extends ReflectionMethod implements ReflectorInterface
{
    private $classname;

    private $docString;

    public function __construct($class, $name)
    {
        parent::__construct($class, $name);
        $this->classname = is_object($class) ? get_class($class) : $class;
    }

    public function getClass()
    {
        return new ClassReflector($this->classname);
    }

    public function isInherited()
    {
        return $this->class !== $this->classname;
    }

    public function isMagic()
    {
        return strpos($this->getName(), '__') === 0;
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

    public function mergeNamedArgs(array $params, $caseSensitive = false, $throw = true)
    {
        // resulting merged parameters will be stored here
        $merged = array();

        // apply strict position parameters and case sensitivity
        foreach ($params as $name => $value) {
            if (is_numeric($name)) {
                $merged[(int) $name] = $value;
            } elseif (!$caseSensitive) {
                $params[strtolower($name)] = $value;
            }
        }

        // we check each parameter and set accordingly
        foreach ($this->getParameters() as $param) {
            $pos  = $param->getPosition();
            $name = $caseSensitive ? $param->getName() : strtolower($param->getName());

            if (array_key_exists($name, $params)) {
                $merged[$pos] = $params[$name];
            } elseif (array_key_exists($pos, $params)) {
                $merged[$pos] = $params[$pos];
            } elseif ($param->isOptional()) {
                $merged[$pos] = $param->getDefaultValue();
            } elseif ($throw) {
                Exception::toss('The required parameter "%s" for "%s::%s()" was not specified.', $param->getName(), $this->getClass()->getName(), $this->getName());
            } else {
                $meged[$pos] = null;
            }
        }

        return $merged;
    }

    public function invokeArgs($instance, array $args = array())
    {
        // only merged named parameters if necessary
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
        // if it's already been retrieved, just return it
        if ($this->docString) {
            return $this->docString;
        }

        // attempt to get it from the current method
        if ($docblock = parent::getDocComment()) {
            $this->docString = $docblock;
            return $this->docString;
        }

        $name = $this->getName();

        // check traits
        foreach ($this->getDeclaringClass()->getTraits() as $trait) {
            // coninue of the method doesn't exist in the $trait
            if (!$trait->hasMethod($name)) {
                continue;
            }

            // attempt to find it in the current $trait
            if ($this->docString = $trait->getMethod($name)->getDocComment()) {
                return $this->docString;
            }
        }

        // check interfaces
        foreach ($this->getDeclaringClass()->getInterfaces() as $iface) {
            // coninue of the method doesn't exist in the interface
            if (!$iface->hasMethod($name)) {
                continue;
            }

            // attempt to find it in the current interface
            if ($this->docString = $iface->getMethod($name)->getDocComment()) {
                return $this->docString;
            }
        }

        return $this->docString;
    }
}