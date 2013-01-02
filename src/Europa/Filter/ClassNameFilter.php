<?php

namespace Europa\Filter;
use Europa\Config\Config;

class ClassNameFilter
{
    private $config = array(
        'prefix' => '',
        'suffix' => '',
        'tokens' => [DIRECTORY_SEPARATOR, '/', '_', ' ', '.']
    );
    
    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
    }
    
    public function __invoke($value)
    {
        // each part is formatted using the upper camel case filter
        $ucc = new UpperCamelCaseFilter;
        
        // add prefix and suffix to value
        $value = $this->config['prefix'] . $value . $this->config['suffix'];
        
        // ensure there aren't any leading or trailing namespace tokens
        $value = trim($value, '\\');
        
        // normalize namespace separators
        $value = str_replace($this->config['tokens']->export(), '\\', $value);
        
        // split into class namespaces and format each namespace part into upper camel case
        $parts = explode('\\', $value);

        foreach ($parts as &$part) {
            $part = $ucc->__invoke($part);
        }
        
        // class names always come out with a leading namespace separator
        $value = implode('\\', $parts);

        return $value;
    }
}