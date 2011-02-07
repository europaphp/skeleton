<?php

namespace Europa\Request;

/**
 * The request class for representing a CLI request.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Cli extends \Europa\Request
{
    /**
     * The cli request method.
     * 
     * @var string
     */
    const METHOD = 'cli';
    
    /**
     * Holds the commands that were executed, not arguments.
     * 
     * @var array
     */
    protected $commands = array();
    
    /**
     * Constructs a CLI request and sets defaults. By default, no layout or
     * view is rendered.
     * 
     * @return \Europa\Request\Cli
     */
    public function __construct()
    {
        $this->parseParams();
    }
    
    /**
     * Returns the request method to call in the controller.
     * 
     * @return string
     */
    public function method()
    {
        return static::METHOD;
    }
    
    /**
     * Parses out the cli request parameters - in unix style - and sets them on
     * the request.
     * 
     * @return \Europa\Request\Cli
     */
    protected function parseParams()
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
                    $this->__set($param, $next);
                    $skip = true;
                } else {
                    $this->__set($param, true);
                }
            } else {
                $this->commands[] = $param;
            }
        }
        
        return $this;
    }
}