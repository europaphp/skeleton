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
     * Parameter requirements.
     * 
     * @var array
     */
    private $requirements = [];
    
    /**
     * Constructs the route and sets required properties.
     * 
     * @param string $expression   The expression for route matching/parsing.
     * @param array  $defaults     The route defaults.
     * @param array  $requirements The parameter requriements.
     * 
     * @return RegexRoute
     */
    public function __construct($expression, array $defaults = [], array $requirements = [])
    {
        $this->expression   = $expression;
        $this->defaults     = $defaults;
        $this->requirements = $requirements;
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
        if (preg_match('#' . $this->expression . '#', (string) $query, $matches)) {
            $matches = array_merge($this->defaults, $matches);

            if (!$this->validateParamsAgainstRequirements($matches)) {
                return;
            }

            return array_filter($matches, function() use (&$matches) {
                $val = key($matches);
                next($matches);
                return !is_numeric($val);
            });
        }
    }

    /**
     * Ensures each parameter is validated against it's requirement.
     * 
     * @param array $params The matched params.
     * 
     * @return true | void
     */
    private function validateParamsAgainstRequirements(array $params)
    {
        foreach ($this->requirements as $name => $regex) {
            if (isset($params[$name]) && !preg_match('!' . $regex . '!', $params[$name])) {
                return;
            }
        }

        return true;
    }
}