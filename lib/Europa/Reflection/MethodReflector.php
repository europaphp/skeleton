<?php

namespace Europa\Reflection;
use LogicException;

/**
 * Extends the base reflection class to provide further functionality such as named
 * parameter merging and calling.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class MethodReflector extends \ReflectionMethod implements Reflectable
{
    private $classname;
    
    /**
     * The cached doc string.
     * 
     * @var string
     */
    private $docString;
    
    /**
     * Overrides the constructor so we can grab information about the method in relation to the class we are reflecting.
     * 
     * @param mixed  $class The class the method we are reflecting is in.
     * @param string $name  The method name.
     * 
     * @return MethodReflector
     */
    public function __construct($class, $name)
    {
        parent::__construct($class, $name);
        $this->classname = is_object($class) ? get_class($class) : $class;
    }
    
    /**
     * Returns the class associated to this method.
     * 
     * @return ClassReflector
     */
    public function getClass()
    {
        return new ClassReflector($this->classname);
    }
    
    /**
     * Returns whether or not the method is inherited.
     * 
     * @return bool
     */
    public function isInherited()
    {
        return $this->class !== $this->classname;
    }
    
    /**
     * Returns the visibility of the current method.
     * 
     * @return string
     */
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
    
    /**
     * Takes the passed named parameters and returns a merged array of the passed parameters and the method's default
     * parameters in the order in which they were defined. If a required parameter is not defined and $throw is true,
     * an exception is thrown indicating the parameter that was not defined. If $throw is false, the required parameter
     * is set to null if not defined.
     * 
     * @param array $params        The parameters to merge.
     * @param bool  $caseSensitive Whether or not to make them case insensitive.
     * @param bool  $throw         Whether or not to throw exceptions if a required parameter is not defined.
     * 
     * @throws LogicException If a required parameter is not specified.
     * 
     * @return array The merged parameters.
     */
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
                throw new LogicException(sprintf(
                    'The required parameter "%s" for "%s::%s()" was not specified.',
                    $param->getName(),
                    $this->getClass()->getName(),
                    $this->getName()
                ));
            } else {
                $meged[$pos] = null;
            }
        }
        
        return $merged;
    }

    /**
     * Instead of just calling with the arguments in their natural order, this method allows
     * the method to be called with arguments which keys match the original method definition
     * of names.
     * 
     * Note, that reflection can only call public members of an object, therefore, you cannot
     * invoke protected or private methods with this method.
     * 
     * @param object $instance The object instance to call the method on.
     * @param array  $args     The named arguments to merge and pass to the method.
     * 
     * @return mixed
     */
    public function invokeNamedArgs($instance, array $args = array())
    {
        // only merged named parameters if necessary
        if ($args && $this->getNumberOfParameters() > 0) {
            return $this->invokeArgs($instance, $this->mergeNamedArgs($args));
        }
        return $this->invoke($instance);
    }

    /**
     * Returns the doc block instance for this method.
     * 
     * @return \Europa\Reflection\DocBlock
     */
    public function getDocBlock()
    {
        return new DocBlock($this->getDocComment());
    }

    /**
     * Returns the docblock for the specified method. If it's not defined, then it
     * goes up the inheritance tree and through its interfaces.
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
        
        // attempt to get it from the current method
        if ($docblock = parent::getDocComment()) {
            $this->docString = $docblock;
            return $this->docString;
        }
        
        // if not, check it's interfaces
        $methodName = $this->getName();
        foreach ($this->getDeclaringClass()->getInterfaces() as $iFace) {
            // coninue of the method doesn't exist in the interface
            if (!$iFace->hasMethod($methodName)) {
                continue;
            }
            
            // attempt to find it in the current interface
            if ($this->docString = $iFace->getMethod($methodName)->getDocComment()) {
                 break;
            }
        }
        
        return $this->docString;
    }
}
