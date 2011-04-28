<?php

namespace Helper;
use Europa\Request\Http;
use Europa\Uri as UriClass;
use Europa\View;

/**
 * A helper for formatting a passed in url.
 * 
 * @category Helpers
 * @package  UriHelper
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Uri
{
    /**
     * The uri instance.
     * 
     * @var \Europa\Uri
     */
    private $uri;
    
    /**
     * Instantiates the url formatter and sets properties.
     * 
     * @param Europa\View $view
     * @param string      $uri
     * @param array       $params
     * 
     * @return \Helper\Uri
     */
    public function __construct(View $view, $uri = null, array $params = array())
    {
        $current   = UriClass::detect();
        $this->uri = new UriClass($uri);
        
        // if the current request uri is the same as the specified one, then merge existing parameters
        if ($current->getRequest() === $this->uri->getRequest()) {
            $this->uri->setParams($current->getParams());
        }
        
        // just in case params are being merged, we override with passed params
        $this->uri->setParams($params);
        
        // if there is no host and it's not an absolute URI, we auto-detect the root
        if (!$this->uri->getHost() && $uri[0] !== '/') {
            $this->uri->setRoot($current->getRoot());
        }
    }
    
    /**
     * Formats the url and returns it.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->uri->toString();
    }
}