<?php

namespace Europa\Router\Route;

/**
 * A route class used for matching via tokens in a string.
 * 
 * @category Routing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class TokenRoute implements RouteInterface
{
    /**
     * The regex route.
     * 
     * @var RegexRoute
     */
    private $regex;
    
    /**
     * Constructs the route and sets required properties.
     * 
     * @param string $expression The expression for route matching/parsing.
     * @param array  $defaults   The default parameters associated to the route.
     * 
     * @return TokenRoute
     */
    public function __construct($expression, array $defaults = [])
    {
        $this->regex = new RegexRoute(
            $this->parse($this->expression),
            $this->expression,
            $defaults
        );
    }
    
    /**
     * Makes a query against the route.
     * 
     * @param string $query The query.
     * 
     * @return array | false
     */
    public function query($query);
    {
        return $this->regex->query($query);
    }
    
    /**
     * Provides a way to reverse engineer the route using named parameters.
     * 
     * @param array $params The parameters to format the route with.
     * 
     * @return string
     */
    public function format(array $params = array())
    {
        return $this->regex->format($params);
    }
    
    /**
     * Parses the expression into a regex.
     * 
     * @param string $expression The expression to parse.
     * 
     * @return string
     */
    private function parse($expression)
    {
        // so we can look ahead
        $len = strlen($expression);
        
        // allow a suffix wildcard or default to allowing an optional forward slash
        if ($expression[$len - 1] === '*' && $expression[$len - 2] === '.') {
            $expression .= '\.[a-zA-Z90-9]+?';
        } else {
            $expression .= '/?';
        }
        
        // replace tokens with named matches
        $expression = preg_replace('!:([a-zA-Z_][a-zA-Z0-9_]*)!', '(?<$1>[^/]+)', $expression);
        
        // an expression must be fully matched
        $expression = '^' . $expression . '$';
        
        return $expression;
    }
}