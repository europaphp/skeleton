<?php

namespace Europa\View\Helper;
use Europa\Flash as FlashLibrary;

/**
 * A helper for retrieving flash messages.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Flash implements \IteratorAggregate
{
    /**
     * The flash message library.
     * 
     * @var FlashLibrary
     */
    private $flash;
    
    /**
     * Constructs and configures a new flash library helper.
     * 
     * @param string $name The name of the flash message queue.
     * @param string $ns   The namespace of the flash message queue.
     * 
     * @return Flash
     */
    public function __construct($name = FlashLibrary::DEFAULT_NAME, $ns = FlashLibrary::DEFAULT_NS)
    {
        $this->flash = new FlashLibrary($name, $ns);
    }
    
    /**
     * Returns whether or not the current queue has any items in it.
     * 
     * @return bool
     */
    public function exists()
    {
        return $this->flash->exists();
    }
    
    /**
     * Returns an iterator representing the message queue.
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return $this->flash->getIterator();
    }
}