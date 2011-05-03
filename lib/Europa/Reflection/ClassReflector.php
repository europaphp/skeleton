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
class ClassReflector extends \ReflectionClass implements Reflectable
{
    /**
     * The cached doc string.
     * 
     * @var string
     */
    private $docString;
    
    /**
     * Overridden to get the \Europa\Reflection\MethodReflector instance for a method.
     * 
     * @param string $method The name of the method to get.
     * 
     * @return \Europa\Reflection\MethodReflector
     */
    public function getMethod($method)
    {
        return new MethodReflector($this->getName(), $method);
    }
    
    /**
     * Returns an array of \Europa\Reflection\MethodReflector instances.
     * 
     * @return array
     */
    public function getMethods()
    {
        $methods = array();
        foreach (parent::getMethods() as $method) {
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
}