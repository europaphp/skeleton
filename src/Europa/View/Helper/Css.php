<?php

namespace Europa\View\Helper;
use Europa\Request\Uri as UriObject;

/**
 * The helper used to format link tags.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Css
{
    /**
     * The default suffix.
     * 
     * @var string
     */
    const SUFFIX = '.css';
    
    /**
     * Formats the link URI.
     * 
     * @return string
     */
    public function compile($path, $xhtml = false)
    {
        $uri   = new UriObject($path);
        $xhtml = $xhtml ? ' /' : '';
        return '<link rel="stylesheet" type="text/css" href="' . $uri . '"' . $xhtml . '>';
    }
}