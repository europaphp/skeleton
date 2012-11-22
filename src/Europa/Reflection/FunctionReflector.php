<?php

namespace Europa\Reflection;
use Europa\Exception\Exception;
use ReflectionFunction;

/**
 * Extends the base reflection class to provide further functionality such as named
 * parameter merging and calling.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class FunctionReflector extends ReflectionFunction implements ReflectorInterface
{
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
                Exception::toss('The required parameter "%s" for function "%s()" was not specified.', $param->getName(), $this->getName());
            } else {
                $meged[$pos] = null;
            }
        }

        return $merged;
    }

    /**
     * Instead of just calling with the arguments in their natural order, this method allows the function to be called with arguments which keys match the original function definition of names.
     * 
     * @param array $args The named arguments to merge and pass to the method.
     * 
     * @return mixed
     */
    public function invokeArgs(array $args = array())
    {
        // only merged named parameters if necessary
        if (func_num_args() === 2 && $this->getNumberOfParameters() > 0) {
            return parent::invokeArgs($this->mergeNamedArgs($args));
        }
        return $this->invoke();
    }

    /**
     * Returns the doc block instance for this method.
     * 
     * @return DocBlock
     */
    public function getDocBlock()
    {
        return new DocBlock($this->getDocComment());
    }
}