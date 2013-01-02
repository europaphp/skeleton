<?php

namespace Europa\Request;
use Europa\Filter\ToStringFilter;

class Cli extends RequestAbstract implements CliInterface
{
    private $commands = array();
    
    public function __construct()
    {
        $this->setMethod(static::METHOD);
        $this->parseCommands();
        $this->parseParams();
    }
    
    public function __toString()
    {
        return trim($this->getCommand() . ' ' . $this->getParamsAsString());
    }
    
    public function setCommand($command)
    {
        $this->commands = preg_split('/\s+/', $command);
        return $this;
    }
    
    public function getCommand()
    {
        return implode(static::T_NS, $this->getCommands());
    }
    
    public function setCommands(array $commands)
    {
        $this->commands = $commands;
        return $this;
    }
    
    public function getCommands()
    {
        return $this->commands;
    }

    public function getParamsAsString()
    {
        $params = '';

        foreach ($this->getParams() as $name => $value) {
            $value   = is_string($value) ? '"' . $value . '"' : (new ToStringFilter)->__invoke($value);
            $params .= ' --' . $name . '=' . $value;
        }

        return trim($params);
    }
    
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