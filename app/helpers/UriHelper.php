<?php

/**
 * A helper for formatting a passed in url.
 * 
 * @category Helpers
 * @package  UrlHelper
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class UriHelper
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
     * @param uropa\View $view
     * @param string      $url
     * @param array       $params
     * 
     * @return UrlHelper
     */
    public function __construct(\Europa\View $view, $url = null, array $params = array())
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
        if ($root = Europa\Request\Http::root()) {
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