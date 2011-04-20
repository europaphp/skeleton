<?php

namespace Europa\Request;
use Europa\Request;

/**
 * The request class for representing a CLI request.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Cli extends Request
{
    /**
     * The cli request method.
     * 
     * @var string
     */
    const METHOD = 'cli';
    
    /**
     * Keeps track of the commands that were passed in the request.
     * 
     * @var array
     */
    private $commands = array();
    
    /**
     * Constructs a cli request and parses out the parameters.
     * 
     * @return \Europa\Request\Cli
     */
    public function __construct()
    {
        $this->parseCommands();
        $this->parseParams();
        $this->setController(str_replace(' ', '\\', $this->getCommand()));
    }
    
    /**
     * Returns the request method to call in the controller.
     * 
     * @return string
     */
    public function getMethod()
    {
        return static::METHOD;
    }
    
    /**
     * Returns the string command that was passed to the console. The command is the part that occurs just before
     * the first argument and after the path to the script. This can contain spaces.
     * 
     * @param string $imploder The string to implode the commands with.
     * 
     * @return string
     */
    public function getCommand($imploder = ' ')
    {
        return implode($imploder, $this->getCommands());
    }
    
    /**
     * Returns an array of the commands that were passed in the request.
     * 
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }
    
    /**
     * Parses out the commands that were used in the request.
     * 
     * @return \Europa\Request\Cli
     */
    private function parseCommands()
    {
        $args = $_SERVER['argv'];
        $cmds = array();
        
        array_shift($args);
        foreach ($args as $arg) {
            if (strpos($arg, '-') !== false) {
                break;
            }
            $this->commands[] = $arg;
        }
        return $this;
    }
    
    /**
     * Parses out the cli request parameters - in unix style - and sets them on
     * the request.
     * 
     * @return \Europa\Request\Cli
     */
    private function parseParams()
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
                
                if ($next !== false && $next[0] !== '-') {
                    $this->setParam($param, $next);
                    $skip = true;
                } else {
                    $this->setParam($param, true);
                }
            }
        }
        
        return $this;
    }
}
