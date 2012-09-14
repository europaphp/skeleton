<?php

namespace Europa\Router;

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
            $this->parse($expression),
            $expression,
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
    public function query($query)
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
        $format = $this->regex->format($params);
        $format = str_replace('.*', '', $format);
        return $format;
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