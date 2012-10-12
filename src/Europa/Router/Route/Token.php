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
class Token
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
     * @param string $expression   The expression for route matching/parsing.
     * @param array  $defaults     The route defaults.
     * @param array  $requirements The parameter requriements.
     * 
     * @return TokenRoute
     */
    public function __construct($expression, array $defaults = [], array $requirements = [])
    {
        $this->regex = new Regex($this->parse($expression), $defaults, $requirements);
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
        return $this->regex->__invoke($query);
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
        if ($expression === '*') {
            return '.*';
        }

        // The parameter regex
        $paramRegex   = ':([a-zA-Z][a-zA-Z0-9_]*)';
        $paramReplace = '(?<$%d>[^/]+)';

        // so we can look ahead
        $len = strlen($expression);

        // optional route parameters are specified by using parenthesis around them
        $expression = preg_replace('!(/)?\(' . $paramRegex . '\)!', '($1?' . sprintf($paramReplace, 2) . ')?', $expression);
        
        // replace required tokens with named matches
        $expression = preg_replace('!' . $paramRegex . '!', sprintf($paramReplace, 1), $expression);

        // routes allow a suffix or a trailing forward slash
        $expression .= '(/|\.[a-z]+)?';
        
        // an expression must be fully matched
        $expression = '^' . $expression . '$';
        
        return $expression;
    }
}