<?php

namespace Europa\Request;

/**
 * Blueprint for HTTP requests.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface CliInterface extends RequestInterface
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
     * Sets the command.
     * 
     * @param string $command The command to set.
     * 
     * @return Cli
     */
    public function setCommand($command);
    
    /**
     * Returns the string command that was passed to the console. The command is the part that occurs just before
     * the first argument and after the path to the script. This can contain spaces.
     * 
     * @return string
     */
    public function getCommand();
    
    /**
     * Sets the commands.
     * 
     * @param array $commands The commands to set.
     * 
     * @return Cli
     */
    public function setCommands(array $commands);
    
    /**
     * Returns an array of the commands that were passed in the request.
     * 
     * @return array
     */
    public function getCommands();

    /**
     * Returns the parameters as a string.
     * 
     * @return string
     */
    public function getParamsAsString();
}