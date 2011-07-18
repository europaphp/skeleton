<?php

namespace Helper;

/**
 * A base helper for rendering js and css script tags.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class Script
{
    /**
     * The prefix to apply to the file path.
     * 
     * @var string
     */
    private $prefix;
    
    /**
     * Each script subclass must implement a way to compile the script tag.
     * 
     * @param stirng $file The file to compile.
     * 
     * @return string
     */
    abstract protected function compile($file);
    
    /**
     * Sets up the script helper using the specified prefix.
     * 
     * @param string $prefix The prefix to use for this script instance.
     * 
     * @return Script
     */
    public function __construct($prefix)
    {
        $this->prefix = trim($prefix, '/');
    }
    
    /**
     * Renders the script tag.
     * 
     * @param string|array $files The file or files to compile.
     * 
     * @return string
     */
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
