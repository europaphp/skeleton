<?php

namespace Europa\View\Helper;
use Europa\Request\Uri as U;

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
     * The default path.
     * 
     * @var string
     */
    const PATH = 'css';
    
    /**
     * The default suffix.
     * 
     * @var string
     */
    const SUFFIX = '.css';
    
    /**
     * The URI used to format the link tag.
     * 
     * @var Uri
     */
    private $uri;
    
    /**
     * Whether or not to format the tag as XHTML.
     * 
     * @var bool
     */
    private $xhtml;
    
    /**
     * Formats a script tag that references a CSS file.
     * 
     * @return Script
     */
    public function __construct($path = self::PATH, $xhtml = false)
    {
        $this->uri   = new U($path . '.css');
        $this->xhtml = $xhtml;
    }
    
    /**
     * Formats the link URI.
     * 
     * @return string
     */
    public function __toString()
    {
        $xhtml = $this->xhtml ? ' /' : '';
        return '<link rel="stylesheet" type="text/css" href="' . $this->uri->__toString() . '"' . $xhtml . '>';
    }
}