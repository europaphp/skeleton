<?php

namespace Europa\Filter;
use Europa\Config\Config;

class MethodNameFilter
{
    private $config = array(
        'prefix' => '',
        'suffix' => ''
    );
    
    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
    }
    
    public function __invoke($value)
    {
        // Which filter is used depends on if there is a prefix.
        $filter = $this->config['prefix'] ? new UpperCamelCaseFilter : new LowerCamelCaseFilter;
        
        // add prefix and suffix to value
        $value = $this->config['prefix'] . $filter->__invoke($value) . $this->config['suffix'];

        return $value;
    }
}