<?php

namespace Europa\View\Helper;
use Europa\Request\Uri as U;

/**
 * The helper used to format script tags.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Js
{
    /**
     * The default path.
     * 
     * @var string
     */
    const PATH = 'js';
    
    /**
     * The default suffix.
     * 
     * @var string
     */
    const SUFFIX = '.js';
    
    /**
     * The URI used to format the script tag.
     * 
     * @var Uri
     */
    private $uri;
    
    /**
     * Formats a script tag that references a JS file.
     * 
     * @return Script
     */
    public function __construct($path = self::PATH)
    {
        $this->uri = new U($path . self::SUFFIX);
    }
    
    /**
     * Formats the script URI.
     * 
     * @return string
     */
    public function __toString()
    {
        return '<script type="text/javascript" src="' . $this->uri->__toString() . '"></script>';
    }
}