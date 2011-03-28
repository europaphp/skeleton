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
     * Returns the request method to call in the controller.
     * 
     * @return string
     */
    public function method()
    {
        return static::METHOD;
    }
}