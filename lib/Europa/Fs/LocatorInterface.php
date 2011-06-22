<?php

namespace Europa\Fs;

/**
 * Basic interface for searching for loadable items.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org
 */
interface LocatorInterface
{
    /**
     * Searches for the item and return its path. If the item is not found it returns false.
     * 
     * @param string $item The item to search for.
     * 
     * @return bool|string
     */
    public function locate($item);
}