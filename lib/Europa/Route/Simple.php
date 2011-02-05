<?php

namespace Europa\Route;

/**
 * A route class that requires URL rewriting that isused for matching via simple expressions.
 * 
 * @category Routing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Simple implements \Europa\Route
{
    /**
     * Whether or not the curent expression contains a wildcard.
     * 
     * @var bool
     */
    private $_hasWildcard = false;
    
    /**
     * The expression to match against each query.
     * 
     * @var string
     */
    private $_expression;
    
    /**
     * The default parameters to bind.
     * 
     * @var array
     */
    private $_defaults;
    
    /**
     * Constructs a new route and sets the expression and default values. It
     * also detects whether or not a wildcard was used in the expression.
     * 
     * @param string $expression The expression used for matching.
     * @param array  $defaults   The default parameters to bind.
     * 
     * @return \Europa\Route\Simple
     */
    public function __construct($expression, array $defaults = array())
    {
        $expressionLength   = strlen($expression);
        $this->_defaults    = $defaults;
        $this->_hasWildcard = $expression[$expressionLength - 1] === '*';
        $this->_expression  = $this->_hasWildcard 
                            ? substr($expression, 0, $expressionLength - 1) 
                            : $expression;
    }
    
    /**
     * Maks a query against the subject using the route's expression.
     * 
     * @todo Rework logic so it is more readable.
     * @todo Thin out logic.
     * 
     * @param string $subject The subject to query.
     * 
     * @return array|false
     */
    public function query($subject)
    {
        $expressionParts = explode('/', $this->_expression);
        $subjectParts    = explode('/', $subject);
        
        // if the expression is longer than the subject, then
        // they don't match
        if (count($expressionParts) > count($subjectParts)) {
            return false;
        }
        
        // if the expression is shorter than the subject, the
        // expression must have a wildcard
        if (count($expressionParts) < count($subjectParts) && !$this->_hasWildcard) {
            return false;
        }
        
        
        // map paramters from the subject
        $params = $this->_defaults;
        while (list($index, $subjectPart) = each($subjectParts)) {
            // check for dynamic prameter binding
            if (strpos($subjectPart, ':') !== false) {
                list($name, $value) = explode(':', $subjectPart);
                $params[$name] = $value;
            }
            
            // the subject may be longer than the expression if a wildcard is used
            if (!isset($expressionParts[$index])) {
                continue;
            }
            
            // grab the part of the expression that corresponds to this subject part
            $expressionPart = $expressionParts[$index];
            
            // if the expression part is empty, then just continue on
            if (!$expressionPart) {
                continue;
            }
            
            // if we are on a named parameter, then set it
            if ($expressionPart[0] === ':') {
                $params[substr($expressionPart, 1)] = $subjectPart;
            // otherwise we need to check to make sure the parts are equal
            } elseif ($expressionPart !== $subjectPart) {
                return false;
            }
        }
        
        return $params;
    }
    
    /**
     * Reverse engineers the expression using the specified parameters.
     * 
     * @param array $params The parameters to use in the expression.
     * 
     * @return string
     */
    public function reverse(array $params = array())
    {
        $reverse = $this->_expression;
        $params  = array_merge($this->_defaults, $params);
        while (list($name, $value) = each($params)) {
            $reverse = str_replace(':' . $name, $value, $reverse);
        }
        return $reverse;
    }
}