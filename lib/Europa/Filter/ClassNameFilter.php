<?php

namespace Europa\Filter;

class ClassNameFilter implements FilterInterface
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
     * @param array $config Custom configuration.
     * 
     * @return \Europa\Filter\ClassNameFilter
     */
    public function __construct(array $config = array())
    {
        $this->config = array_merge($this->config, $config);
    }
    
    /**
     * Filters the specified value.
     * 
     * @param mixed $value The value to filter.
     * 
     * @return mixed
     */
    public function filter($value)
    {
        // each part is formatted using the upper camel case filter
        $ucc = new UpperCamelCaseFilter;
        
        // add prefix and suffix to value
        $value = $this->config['prefix'] . $value . $this->config['suffix'];
        
        // ensure there aren't any leading or trailing namespace tokens
        $value = trim($value, '\\');
        
        // normalize namespace separators
        $value = str_replace(array(DIRECTORY_SEPARATOR, '/', '_'), '\\', $value);
        
        // split into class namespaces and format each namespace part into upper camel case
        $parts = explode('\\', $value);
        foreach ($parts as &$part) {
            $part = $ucc->filter($part);
        }
        
        // class names always come out with a leading namespace separator
        $value = '\\' . implode('\\', $parts);
        return $value;
    }
}
