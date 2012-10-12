<?php

namespace Europa\Router\Adapter;
use ArrayIterator;
use Europa\Router\TokenRoute;
use InvalidArgumentException;

/**
 * Reads an INI file and creates routes from it.
 * 
 * @category Routing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Ini
{
    /**
     * The Ini file path.
     * 
     * @var string
     */
    private $file;
    
    /**
     * Constructs a new route INI file provider.
     * 
     * @param string $file The ini file.
     * 
     * @return Ini
     */
    public function __construct($file)
    {
        if (!$this->file = realpath($file)) {
            throw new InvalidArgumentException(sprintf('The file "%s" is not a valid routes INI file.', $file));
        }
    }

    /**
     * Returns an array of routes.
     * 
     * @return array
     */
    public function __invoke()
    {
        return parse_ini_file($this->file);
    }
}