<?php

namespace Europa\Filter;
use Europa\Config\Config;

/**
 * Returns a class name from the specified value.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class MethodNameFilter
{
    /**
     * Default configuration.
     * 
     * @var array
     */
    private $config = array(
        'prefix' => '',
        'suffix' => ''
    );
    
    /**
     * Sets up the class name filter.
     * 
     * @param mixed $config Custom filter configuration.
     * 
     * @return MethodNameFilter
     */
    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
    }
    
    /**
     * Filters the value and returns the filtered value.
     * 
     * @param mixed $value The value to filter.
     * 
     * @return mixed
     */
    public function __invoke($value)
    {
        // Which filter is used depends on if there is a prefix.
        $filter = $this->config['prefix'] ? new UpperCamelCaseFilter : new LowerCamelCaseFilter;
        
        // add prefix and suffix to value
        $value = $this->config['prefix'] . $filter->__invoke($value) . $this->config['suffix'];

        return $value;
    }
}