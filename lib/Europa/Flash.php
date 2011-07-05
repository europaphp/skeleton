<?php

namespace Europa;

/**
 * A library for handling flash messages via sessions.
 * 
 * @category FlashMessages
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Flash implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /**
     * The default key name.
     * 
     * @var string
     */
    const DEFAULT_NAME = 'default';
    
    /**
     * The default namespace.
     * 
     * @var string
     */
    const DEFAULT_NS = '\Europa\Flash';
    
    /**
     * The current key name.
     * 
     * @var string
     */
    private $name;
    
    /**
     * The current namespace.
     * 
     * @var string
     */
    private $ns;
    
    /**
     * Sets up a new flash message.
     * 
     * @param string $name The key name.
     * @param string $ns   The namespace.
     * 
     * @return \Europa\Flash
     */
    public function __construct($name = self::DEFAULT_NAME, $ns = self::DEFAULT_NS)
    {
        // start a session if one doesn't exist
        if (!session_id()) {
            session_start();
        }
        
        $this->name = $name;
        $this->ns   = $ns;
        
        $key = $this->getKey();
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = array();
        }
    }
    
    /**
     * Adds a message to the list.
     * 
     * @param string $message The message to add.
     * 
     * @return \Europa\Flash
     */
    public function add($message)
    {
        $this->offsetSet(null, $message);
        return $this;
    }
    
    /**
     * Sets a message at at given offset. If no offset is specified, then it is appended to the message list.
     * 
     * @param mixed  $offset  The offset to set the message at.
     * @param string $message The message to set.
     * 
     * @return void
     */
    public function offsetSet($offset, $message)
    {
        $key = $this->getKey();
        if (!is_numeric($offset)) {
            $offset = count($_SESSION[$key]);
        }
        $_SESSION[$key][$offset] = $message;
    }
    
    /**
     * Returns the message at the specified offset if it exists.
     * 
     * @param mixed $offset The message to get.
     * 
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $key = $this->getKey();
        if (isset($_SESSION[$key][$offset])) {
            return $_SESSION[$key][$offset];
        }
        return null;
    }
    
    /**
     * Returns whether or not a message at the specified offset exists.
     * 
     * @param mixed $offset The offset to check.
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($_SESSION[$this->getKey()][$offset]);
    }
    
    /**
     * If a message exists at the specified offset it is removed.
     * 
     * @param mixed $offset The offset to remove the message from.
     * 
     * @return void
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($_SESSION[$this->getKey()][$offset]);
        }
    }
    
    /**
     * Returns the number of messages in the list.
     * 
     * @return int
     */
    public function count()
    {
        return count($_SESSION[$this->getKey()]);
    }
    
    /**
     * Returns an iterator for the message list.
     * 
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $key = $this->getKey();
        
        // set up iterator and reset session storage
        $iterator       = new \ArrayIterator($_SESSION[$key]);
        $_SESSION[$key] = array();
        
        return $iterator;
    }
    
    /**
     * Returns whether or not the any items exist in the list.
     * 
     * @return bool
     */
    public function exists()
    {
        return $this->count() > 0;
    }
    
    /**
     * Returns the session variable key.
     * 
     * @return string
     */
    private function getKey()
    {
        return $this->name . $this->ns;
    }
}