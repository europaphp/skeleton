<?php

namespace Europa\Router\Route;

/**
 * A route class used for matching via regular expressions.
 * 
 * @category Routing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class RegexRoute implements RouteInterface
{
    /**
     * The expression used to match the route.
     * 
     * @var string
     */
    private $expression;
    
    /**
     * Since it is very difficult to reverse engineer a regular expression a reverse engineering string is used to
     * reverse engineer the route back into a URI. This allows for fluid links.
     * 
     * @var string
     */
    private $reverse;
    
    /**
     * The mapping used to map matched parameters or bind hard-coded parameters.
     * 
     * @var array
     */
    private $map;
    
    /**
     * Constructs the route and sets required properties.
     * 
     * @param string $expression The expression for route matching/parsing.
     * @param string $reverse    The string used to reverse engineer the route.
     * @param array  $map        The string to use when reverse engineering the expression.
     * 
     * @return \Europa\Route
     */
    public function __construct($expression, $reverse = null, array $map = array())
    {
        $this->expression = $expression;
        $this->reverse    = $reverse;
        $this->map        = $map;
    }
    
    /**
     * Reverse engineers the current route to produce a formatted string.
     * 
     * @param array $params The parameters used to reverse engineer the route.
     * 
     * @return string
     */
    public function reverse(array $params = array())
    {
        $parsed = $this->reverse;
        foreach (array_merge($this->map, $params) as $name => $value) {
            $parsed = str_replace(':' . $name, $value, $parsed);
        }
        return $parsed;
    }
    
    /**
     * Matches the passed subject to the route. Can be extended to provide a custom routing algorithm. Returns the
     * matched parameters.
     * 
     * @param string $subject The URI to match against the current route definition.
     * 
     * @return array|bool
     */
    public function query($subject)
    {
        // we make sure the subject is a string, or can be converted to one
        $subject = (string) $subject;
        $params  = array();
        
        // get some matches or return false if not matched
        if (!preg_match('#' . $this->expression . '#', $subject, $matches)) {
            return false;
        }
        
        // the first match is useless to us
        array_shift($matches);
        $matches = array_filter($matches, 'trim');
        
        // map default and hardcoded values
        foreach ($this->map as $name => $value) {
            // a string key denotes a hardcoded value
            if (is_string($name)) {
                $params[$name] = $value;
            // a numeric key denotes a value mapped to a matched index
            } else {
                $params[$value] = $matches[$name];
            }
        }
        
        // override any default/hardcoded values with matched values
        foreach ($matches as $name => $value) {
            if (is_string($name)) {
                $params[$name] = $value;
            }
        }
        
        // return the parameters
        return $params;
    }
}
