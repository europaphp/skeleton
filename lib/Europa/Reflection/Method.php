<?php

namespace Europa\Reflection;

/**
 * Extends the base reflection class to provide further functionality such as named
 * parameter merging and calling.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Method extends \ReflectionMethod
{
    /**
     * The cached return types.
     * 
     * @var array
     */
    private $returnTypes;
    
    /**
     * Takes the passed named parameters and returns a merged array of the passed parameters
     * and the method's default parameters in the order in which they were defined.
     * 
     * @param array $params        The parameters to merge.
     * @param bool  $caseSensitive Whether or not to make them case insensitive.
     * 
     * @return array The merged parameters.
     */
    public function mergeNamedArgs(array $params, $caseSensitive = false)
    {
        // resulting merged parameters will be stored here
        $merged = array();
        
        // apply case-insensitivity
        if (!$caseSensitive) {
            foreach ($params as $name => $value) {
                $params[strtolower($name)] = $value;
            }
        }

        // we check each parameter and set accordingly
        foreach ($this->getParameters() as $param) {
            $pos  = $param->getPosition();
            $name = $caseSensitive ? $name : strtolower($param->getName());
            
            // apply named parameters
            if (array_key_exists($name, $params)) {
                $merged[$pos] = $params[$name];
            // set default values
            } elseif ($param->isOptional()) {
                $merged[$pos] = $param->getDefaultValue();
            // throw exceptions when required params aren't defined
            } else {
                $class = get_class($this);
                throw new \Europa\Reflection\Exception(
                    "Parameter {$param->getName()} for {$this->getDeclaringClass()->getName()}->{$this->getName()}() was not defined."
                );
            }
        }
        
        return $merged;
    }
    
    /**
     * Returns the return value for the specified method as an array.
     * 
     * @return array
     */
    public function getReturnTypes()
    {
        // if already found, return it
        if ($this->returnTypes) {
            return $this->returnTypes;
        }
        
        // docblock must exist
        if (!$doc = $this->getDocCommentRecursive()) {
            return array();
        }
        
        // attempt to get the return part of the docblock
        $doc = explode(' * @return', $doc);
        if (!isset($doc[1])) {
            return array();
        }
        
        // parse out the return types and cache it
        $doc = trim($doc[1]);
        $doc = explode(' ', $doc);
        $doc = explode('|', $doc[0]);
        for ($i = 0; $i < count($doc); $i++) {
            $doc[$i] = trim($doc[$i]);
        }
        $this->returnTypes = $doc;
        
        // return parsed
        return $this->returnTypes;
    }
    
    /**
     * Checks the $value and returns whether or not it is valid when compared
     * to the method return types.
     * 
     * @param mixed $value The value to check against $types.
     * 
     * @return bool
     */
    public function isValidReturnValue($value)
    {
        // get the type of the value
        $valueType = strtolower(gettype($value));
        if ($valueType === 'object') {
            $valueType = get_class($value);
        }
        
        $types = $this->getReturnTypes();
        
        // if there are no types, then it is valid
        if (!$types) {
            return true;
        }
        
        // go through and check each type
        // if it matches one, then it's fine
        foreach ($types as $type) {
            // "mixed" means everything
            if ($type === 'mixed') {
                return true;
            }
            
            // check actual type against specified type
            if ($valueType === $type) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Returns the docblock for the specified method. If it's not defined, then it
     * goes up the inheritance tree and through its interfaces.
     * 
     * @todo Implement parent class method docblock sniffing not just interfaces.
     * 
     * @return string|null
     */
    public function getDocCommentRecursive()
    {
        // if it's already been retrieved, just return it
        if ($this->docblock) {
            return $this->docblock;
        }
        
        // attempt to get it from the current method
        $docblock = $this->method->getDocComment();
        if ($docblock) {
            $this->docblock = $docblock;
            return $this->docblock;
        }
        
        // if not, check it's interfaces
        $methodName = $this->method->getName();
        foreach ($this->method->getDeclaringClass()->getInterfaces() as $iFace) {
            // coninue of the method doesn't exist in the interface
            if (!$iFace->hasMethod($methodName)) {
                continue;
            }
            
            // attempt to find it in the current interface
            if ($this->docblock = $iFace->getMethod($methodName)->getDocComment()) {
                 break;
            }
        }
        
        return $this->docblock;
    }
}