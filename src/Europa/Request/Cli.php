<?php

namespace Europa\Request;
use Europa\Filter\ToStringFilter;

/**
 * The request class for representing a CLI request.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Cli extends RequestAbstract implements CliInterface
{
    /**
     * The cli request method.
     * 
     * @var string
     */
    const METHOD = 'cli';
    
    /**
     * The namespace token.
     * 
     * @var string
     */
    const T_NS = ' ';
    
    /**
     * Keeps track of the commands that were passed in the request.
     * 
     * @var array
     */
    private $commands = array();
    
    /**
     * Constructs a cli request and parses out the parameters.
     * 
     * @return Cli
     */
    public function __construct()
    {
        $this->setMethod(static::METHOD);
        $this->parseCommands();
        $this->parseParams();
    }
    
    /**
     * Converts the request to a string representation.
     * 
     * @return string
     */
    public function __toString()
    {
        return trim($this->getCommand() . ' ' . $this->getParamsAsString());
    }
    
    /**
     * Sets the command.
     * 
     * @param string $command The command to set.
     * 
     * @return Cli
     */
    public function setCommand($command)
    {
        $this->commands = preg_split('/\s+/', $command);
        return $this;
    }
    
    /**
     * Returns the string command that was passed to the console. The command is the part that occurs just before
     * the first argument and after the path to the script. This can contain spaces.
     * 
     * @return string
     */
    public function getCommand()
    {
        return implode(static::T_NS, $this->getCommands());
    }
    
    /**
     * Sets the commands.
     * 
     * @param array $commands The commands to set.
     * 
     * @return Cli
     */
    public function setCommands(array $commands)
    {
        $this->commands = $commands;
        return $this;
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
     * Returns the parameters as a string.
     * 
     * @return string
     */
    public function getParamsAsString()
    {
        $params = '';

        foreach ($this->getParams() as $name => $value) {
            $value   = is_string($value) ? '"' . $value . '"' : (new ToStringFilter)->__invoke($value);
            $params .= ' --' . $name . '=' . $value;
        }

        return trim($params);
    }
    
    /**
     * Parses out the commands that were used in the request.
     * 
     * @return Cli
     */
    private function parseCommands()
    {
        $args = (isset($_SERVER['argv'])) ? $_SERVER['argv'] : array();
        $cmds = array();
        
        array_shift($args);
        
        foreach ($args as $arg) {
            if (strpos($arg, '-') === 0) {
                break;
            }
            
            $this->commands[] = $arg;
        }
        
        return $this;
    }
    
    /**
     * Parses out the cli request parameters - in unix style - and sets them on the request.
     * 
     * @return Cli
     */
    private function parseParams()
    {
        $args = (isset($_SERVER['argv'])) ? $_SERVER['argv'] : array();
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