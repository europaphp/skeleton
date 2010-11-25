<?php

/**
 * A helper for formatting a passed in url.
 * 
 * @category Helpers
 * @package  UrlHelper
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart http://europaphp.org/license
 */
class UrlHelper implements Europa_View_Helper
{
    /**
     * The url to format.
     * 
     * @var string
     */
    private $_url = null;
    
    /**
     * The parameters to use.
     * 
     * @var array
     */
    private $_params = array();
    
    /**
     * Instantiates the url formatter and sets properties.
     * 
     * @param Europa_View $view
     * @param string      $url
     * @param array       $params
     * 
     * @return urlHelper
     */
    public function __construct(Europa_View $view, $url = null, array $params = array())
    {
        $this->_url    = $url;
        $this->_params = $params;
    }
    
    /**
     * Formats the url and returns it.
     */
    public function __toString()
    {
        $url = Europa_Request_Http::root();
        
        if ($this->_url) {
            $url .= '/' . ltrim($this->_url, '/');
        }
        
        if ($this->_params) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
}