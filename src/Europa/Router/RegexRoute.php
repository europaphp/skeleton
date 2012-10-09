<?php

namespace Europa\Router;

/**
 * A route class used for matching via regular expressions.
 * 
 * @category Routing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class RegexRoute
{
    /**
     * The result that is returned if the route is matched.
     * 
     * @var mixed
     */
    private $defaults;
    
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
     * Constructs the route and sets required properties.
     * 
     * @param string $expression The expression for route matching/parsing.
     * @param mixed  $reverse    The string used to reverse engineer the route.
     * @param string $defaults   The default values to merge with the matched values.
     * 
     * @return RegexRoute
     */
    public function __construct($expression, $reverse, array $defaults = [])
    {
        $this->expression = $expression;
        $this->reverse    = $reverse;
        $this->defaults   = $defaults;
    }
    
    /**
     * Makes a query against the route.
     * 
     * @param string $query The query.
     * 
     * @return array | false
     */
    public function __invoke($query)
    {
        if (preg_match('#' . $this->expression . '#', (string) $query, $matches)) {
            return array_merge($this->defaults, array_filter($matches, function() use (&$matches) {
                $val = key($matches);
                next($matches);
                return !is_numeric($val);
            }));
        }
        
        return false;
    }
}