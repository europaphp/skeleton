<?php

/**
 * A helper for formatting a passed in URI.
 * 
 * @category Helpers
 * @package  UriHelper
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class UriHelper implements Europa_View_Helper
{
    private $_uri = null;
    
    private $_params = array();
    
    public function __construct(Europa_View $view, array $args = array())
    {
        if (isset($args[0])) {
            $this->_uri = $args[0];
        }
        
        if (isset($args[1])) {
            $this->_uri = $args[1];
        }
    }
    
    public function __toString()
    {
        return Europa_Request_Http::getActiveInstance()->formatUri($this->_uri, $this->_params);
    }
}