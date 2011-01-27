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
}