<?php

namespace Helper;
use Europa\Request\Http;
use Europa\View;

/**
 * A helper for formatting a passed in url.
 * 
 * @category Helpers
 * @package  UrlHelper
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Uri
{
    /**
     * The url to format.
     * 
     * @var string
     */
    protected $url = null;
    
    /**
     * The parameters to use.
     * 
     * @var array
     */
    protected $params = array();
    
    /**
     * Instantiates the url formatter and sets properties.
     * 
     * @param Europa\View $view
     * @param string      $url
     * @param array       $params
     * 
     * @return \Helper\Uri
     */
    public function __construct(View $view, $url = null, array $params = array())
    {
        $this->url    = $url;
        $this->params = $params;
    }
    
    /**
     * Formats the url and returns it.
     * 
     * @return string
     */
    public function __toString()
    {
        $url = '/';
        if ($root = Http::create()->getRootUri()) {
            $url .= $root . '/';
        }
        if ($this->url) {
            $url .= ltrim($this->url, '/');
        }
        if ($this->params) {
            $url .= '?' . http_build_query($this->params);
        }
        return $url;
    }
}