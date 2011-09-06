<?php

namespace Europa\Fs\Locator;

/**
 * Interface for defining file locating relative to many different paths.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface LocatorInterface
{
    /**
     * Searches for the specified file and returns the file if found or false if not found.
     * 
     * @param string $file The file to search for.
     * 
     * @return bool|string
     */
    public function locate($file);
}