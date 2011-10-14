<?php

namespace Europa\Filter;

/**
 * Uses a callback to filter the specified value.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class CallbackFilter implements FilterInterface
{
    /**
     * The callback to execute.
     * 
     * @var mixed
     */
    private $callback;
    
    /**
     * Sets up the filter.
     * 
     * @param mixed $callback The callback.
     * 
     * @return \Europa\Filter\CallbackFilter
     */
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('The value specified for $callback is not callable.');
        }
        $this->callback = $callback;
    }
    
    /**
     * Filters the value and returns the filtered value.
     * 
     * @param mixed $value The value to filter.
     * 
     * @return mixed
     */
    public function filter($value)
    {
        return call_user_func($this->callback, $value);
    }
}
