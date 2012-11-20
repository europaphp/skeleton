<?php

namespace Europa\Router\Adapter;
use Europa\Exception\Exception;

/**
 * Reads configuration options from a PHP file.
 * 
 * @category Config
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Php
{
    /**
     * The file we are reading from.
     * 
     * @var string
     */
    private $file;

    /**
     * Sets up a new adapter.
     * 
     * @param string $file The file to read configuration options from.
     * 
     * @return Php
     */
    public function __construct($file)
    {
        if (!is_file($this->file = $file)) {
            Exception::toss('The PHP configuration file "%s" does not exist.', $file);
        }
    }

    /**
     * Returns an array of configuration options.
     * 
     * @return array
     */
    public function __invoke()
    {
        return include $this->file;
    }
}