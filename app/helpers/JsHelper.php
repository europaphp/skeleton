<?php

/**
 * JavaScript auto-loader.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class JsHelper extends ScriptHelper
{
    /**
     * The default js path.
     * 
     * @var string
     */
    protected static $defaultPath = 'js';
    
    /**
     * The default suffix for js files.
     * 
     * @var string
     */
    protected static $defaultSuffix = 'js';
    
    /**
     * Builds the tag.
     * 
     * @param string $file The file to build the tag for.
     * 
     * @return string
     */
    protected function compileTag($file)
    {
        return '<script type="text/javascript" src="' . $file . '"></script>';
    }
}