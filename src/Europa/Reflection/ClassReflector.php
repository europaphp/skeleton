<?php

namespace Europa\Reflection;
use Europa\Reflection\MethodReflector;

/**
 * Extension to \ReflectionClass to implement doc block getting/parsing.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class ClassReflector extends \ReflectionClass implements ReflectorInterface
{
    /**
     * The cached doc string.
     * 
     * @var string
     */
    private $docString;
    
    /**
     * Checks to see if the class is of the specified type or has the specified trait.
     * 
     * @param string $type The type to check against.
     * 
     * @return bool
     */
    public function is($type)
    {
        return $this->getName() === $type
            || $this->isSubclassOf($type)
            || in_array($type, $this->getTraitNames());
    }
    
    /**
     * Returns if the class derives from any of the specified types.
     * 
     * @param array $types The types to check against.
     * 
     * @return bool
     */
    public function isAny(array $types)
    {
        foreach ($types as $type) {
            if ($this->is($type)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns if the class derives from all of the specified types.
     * 
     * @param array $types The types to check against.
     * 
     * @return bool
     */
    public function isAll(array $types)
    {
        foreach ($types as $type) {
            if (!$this->is($type)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Returns an array of parent class reflectors. The top-most parent is the first item and the last is the current.
     * 
     * @return array
     */
    public function getTree()
    {
        $tree      = [];
        $reflector = $this;
        while ($reflector) {
            $tree[]    = $reflector;
            $reflector = $reflector->getParentClass();
        }
        return array_reverse($tree);
    }
    
    /**
     * Returns an array of parent class names. The top-most parent is the first item and the last is the current.
     * 
     * @return array
     */
    public function getTreeNames()
    {
        $tree = [];
        foreach ($this->getTree() as $parent) {
            $tree[] = $parent->getName();
        }
        return $tree;
    }
    
    /**
     * Overridden to get the \Europa\Reflection\MethodReflector instance for a method.
     * 
     * @param string $method The name of the method to get.
     * 
     * @return MethodReflector
     */
    public function getMethod($method)
    {
        return new MethodReflector($this->getName(), $method);
    }
    
    /**
     * Returns an array of \Europa\Reflection\MethodReflector instances.
     * 
     * @param string $filter An optional filter as specified in parent documentation.
     * 
     * @return array
     */
    public function getMethods($filter = -1)
    {
        $methods = array();
        foreach (parent::getMethods($filter) as $method) {
            $methods[] = $this->getMethod($method->getName());
        }
        return $methods;
    }

    /**
     * Returns the doc block for the class.
     * 
     * @return \Europa\Reflection\DocBlock
     */
    public function getDocBlock()
    {
        return new DocBlock($this->getDocComment());
    }

    /**
     * Returns the docblock for the specified class. If it's not defined, then it
     * goes up the inheritance tree until it finds one.
     * 
     * @todo Implement parent class method docblock sniffing not just interfaces.
     * 
     * @return string|null
     */
    public function getDocComment()
    {
        // if it's already been retrieved, just return it
        if ($this->docString) {
            return $this->docString;
        }

        // check to see if it's here first
        if ($docString = parent::getDocComment()) {
            $this->docString = $docString;
            return $docString;
        }

        // go through each parent class
        $class = $this->getParentClass();
        while ($class) {
            if ($docString = $class->getDocComment()) {
                $this->docString = $docString;
                break;
            }
            $class = $class->getParentClass();
        }

        // then go through each interface
        foreach ($this->getInterfaces() as $iFace) {
            if ($docString = $iFace->getDocComment()) {
                $this->docString = $docString;
                break;
            }
        }
        
        return $this->docString;
    }
    
    /**
     * Creates a new instance using named parameters.
     * 
     * @param array $args The named parameters.
     * 
     * @return mixed
     */
    public function newInstanceArgs(array $args = null)
    {
        if ($this->hasMethod('__construct')) {
            return parent::newInstanceArgs($this->getMethod('__construct')->mergeNamedArgs($args));
        }
        return $this->newInstance();
    }
}