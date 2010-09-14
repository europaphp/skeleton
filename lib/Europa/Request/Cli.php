<?php

/**
 * The request class for representing a CLI request.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Request_Cli extends Europa_Request
{
    /**
     * Holds the commands that were executed, not arguments.
     * 
     * @var array
     */
    protected $_commands = array();
    
    /**
     * Constructs a CLI request and sets defaults. By default, no layout or
     * view is rendered.
     * 
     * @return Europa_Request_Cli
     */
    public function __construct()
    {
        $this->_parseParams();
    }
    
    /**
     * Converts the request back into the original string representation.
     * 
     * @return string
     */
    public function __toString()
    {
        return implode(' ', $this->_commands);
    }
    
    /**
     * Parses out the cli request parameters - in unix style - and sets them on
     * the request.
     * 
     * @return Europa_Request_Cli
     */
    protected function _parseParams()
    {
        $args = $_SERVER['argv'];
        $skip = false;
        array_shift($args);
        foreach ($args as $index => $param) {
            if ($skip) {
                $skip = false;
                continue;
            }
            
            // allow dash prefixing
            if ($param[0] === '-') {
                $cut   = $param[1] === '-' ? 2 : 1;
                $param = substr($param, $cut, strlen($param));
                $next  = isset($args[$index + 1]) ? $args[$index + 1] : false;
            
                if ($next && $next[0] !== '-') {
                    $this->setParam($param, $next);
                    $skip = true;
                } else {
                    $this->setParam($param, true);
                }
            } else {
                $this->_commands[] = $param;
            }
        }
        
        return $this;
    }
}