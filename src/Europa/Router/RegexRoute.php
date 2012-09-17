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
class RegexRoute implements RouteInterface
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
    public function query($query)
    {
        if (preg_match('#' . $this->expression . '#', $query, $matches)) {
            return array_merge($this->defaults, array_filter($matches, function() use (&$matches) {
                $val = key($matches);
                next($matches);
                return !is_numeric($val);
            }));
        }
        return false;
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
        $parsed = $this->reverse;
        $params = array_merge($this->defaults, $params);
        foreach (array_merge($this->defaults, $params) as $name => $value) {
            $parsed = str_replace(':' . $name, $value, $parsed);
        }
        return $parsed;
    }
}