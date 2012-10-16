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
class Regex
{
    /**
     * The expression used to match the route.
     * 
     * @var string
     */
    private $expression;

    /**
     * Default parameters.
     * 
     * @var array
     */
    private $defaults = [];
    
    /**
     * Constructs the route and sets required properties.
     * 
     * @param string $expression The expression for route matching/parsing.
     * @param array  $defaults   The route defaults.
     * 
     * @return RegexRoute
     */
    public function __construct($expression, array $defaults = [])
    {
        $this->expression = $expression;
        $this->defaults   = $defaults;
    }
    
    /**
     * Makes a query against the route.
     * 
     * @param string $query The query.
     * 
     * @return array | void
     */
    public function __invoke($query)
    {
        if (preg_match('!' . $this->expression . '!', (string) $query, $matches)) {
            $matches = array_merge($this->defaults, $matches);

            return array_filter($matches, function() use (&$matches) {
                $val = key($matches);
                next($matches);
                return !is_numeric($val);
            });
        }
    }
}