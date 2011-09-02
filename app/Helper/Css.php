<?php

namespace Helper;

/**
 * Helper for generating css link tags.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Css extends Script
{
    /**
     * Compiles the specified file into a tag.
     * 
     * @param string $file The file to compile.
     * 
     * @return string
     */
    protected function compile($file)
    {
        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$file}.css\" />";
    }
}