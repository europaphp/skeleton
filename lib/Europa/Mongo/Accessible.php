<?php

/**
 * Interface that defines the implementation for classes that are
 * iterable, array accessible and countable. They implementing
 * classes must also make sure they provide a normal array method
 * and a mongo array method.
 * 
 * @category Mongo
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
interface Europa_Mongo_Accessible extends ArrayAccess, Iterator, Countable
{
    /**
     * Converts the object into an array.
     * 
     * @return array
     */
    public function toArray();
    
    /**
     * Converts the object into a mongo-style array.
     * 
     * @return array
     */
    public function toMongoArray();
}