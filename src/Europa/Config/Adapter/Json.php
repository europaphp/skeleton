<?php

namespace Europa\Config\Adapter;
use Europa\Exception\Exception;

/**
 * Reads configuration options from a JSON file.
 * 
 * @category Config
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Json
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
     * @return Json
     */
    public function __construct($file)
    {
        if (!is_file($this->file = $file)) {
            Exception::toss('The JSON config file "%s" does not exist.', $file);
        }
    }

    /**
     * Returns an array of configuration options.
     * 
     * @return array
     */
    public function __invoke()
    {
        return json_decode(file_get_contents($this->file));
    }
}