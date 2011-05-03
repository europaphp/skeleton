<?php

namespace Helper;
use Helper\Script;

/**
 * Stylesheet auto-loader.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Css extends Script
{
    /**
     * The default css path.
     * 
     * @var string
     */
    protected static $defaultPath = 'css';
    
    /**
     * The default suffix for css files.
     * 
     * @var string
     */
    protected static $defaultSuffix = 'css';
    
    /**
     * Builds the tag.
     * 
     * @param string $file The file to build the tag for.
     * 
     * @return string
     */
    protected function compileTag($file)
    {
        return '<link rel="stylesheet" type="text/css" href="' . $file . '" />';
    }
}