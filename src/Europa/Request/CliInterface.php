<?php

namespace Europa\Request;

interface CliInterface extends RequestInterface
{
    const METHOD = 'cli';
    
    const T_NS = ' ';
    
    public function setCommand($command);
    
    public function getCommand();
    
    public function setCommands(array $commands);
    
    public function getCommands();

    public function getParamsAsString();
}