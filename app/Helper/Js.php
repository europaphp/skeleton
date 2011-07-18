<?php

namespace Helper;

/**
 * A helper for generating JavaScript script tags.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Js extends Script
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
        return "<script type=\"text/javascript\" src=\"{$file}.js\"></script>";
    }
}