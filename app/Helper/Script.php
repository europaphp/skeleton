<?php

namespace Helper;

abstract class Script
{
    private $prefix;
    
    abstract protected function compile($file);
    
    public function __construct($prefix)
    {
        $this->prefix = trim($prefix, '/');
    }
    
    public function render($files)
    {
        $str = '';
        foreach ((array) $files as $file) {
            $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
            $file = trim($file, '/');
            $file = new Uri($this->prefix . '/' . $file);
            $str .= $this->compile($file);
            $str .= PHP_EOL;
        }
        return $str;
    }
}