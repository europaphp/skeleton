<?php

/**
 * The main document class used for MongoDB document manipulation.
 * 
 * @category Mongo
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Mongo_Cursor extends MongoCursor implements ArrayAccess, Countable
{
    /**
     * The position of the cursor.
     * 
     * @var int
     */
    private $_position = 0;
    
    /**
     * Whether or not the cursor has been executed.
     * 
     * @var bool
     */
    private $_isExecuted = false;
    
    /**
     * The class name of the document object to instantiate for every document.
     * 
     * @var string
     */
    private $_class;
    
    /**
     * The connection used for the cursor.
     * 
     * @var Europa_Mongo_Connection
     */
    private $_connection;
    
    /**
     * Constructs a new cursor so we can track the connection and set a default class.
     * 
     * @param Europa_Mongo_Connection $connection
     * @param string $collection
     * @param array $query
     * @param array $fields
     * @return Europa_Mongo_Cursor
     */
    public function __construct(Europa_Mongo_Connection $connection, $collection, array $query = array(), array $fields = array())
    {
        // first thing is first, call the parent
        parent::__construct($connection, $collection, $query, $fields);
        
        // set a connection and default class
        $this->_connection = $connection;
        $this->setClass(Europa_String::create($collection)->replace('.', '_')->toClass());
    }
    
    /**
     * Converts the cursor to an array.
     * 
     * @return array
     */
    public function toArray()
    {
        $array = array();
        foreach ($this as $value) {
            $array[] = $value instanceof Europa_Mongo_Document ? $value->toArray() : $value;
        }
        return $array;
    }
    
    /**
     * Overridden to mark the cursor as executed and to rewind it if it
     * is on it's initial execution. This doesn't do anything if it's
     * already been executed.
     * 
     * @return Europa_Mongo_Cursor
     */
    public function doQuery()
    {
        if (!$this->isExecuted()) {
            parent::doQuery();
            $this->rewind();
            $this->_isExecuted = true;
        }
        return $this;
    }
    
    /**
     * Overridden to return a document instance if specified. Also, this
     * makes sure that the query is executed and re-wound if not queried
     * yet.
     * 
     * @return mixed
     */
    public function current()
    {
        $this->doQuery();
        
        // if no class is specified, just return an array
        if (!$this->_class) {
            return parent::current();
        }
        
        // if a class is specified, instantiate it, fill it and return it
        $class = $this->_class;
        $class = new $class;
        return $class->setConnection($this->getConnection())->fill(parent::current());
    }
    
    /**
     * Overridden to increment the internal position tracker.
     * 
     * @return void
     */
    public function next()
    {
        ++$this->_position;
        parent::next();
    }
    
    /**
     * Overridden to reset the internal position tracker.
     * 
     * @return void
     */
    public function rewind()
    {
        $this->_position = 0;
        parent::rewind();
    }
    
    /**
     * Currently this does nothing and just satisfies the ArrayAccess
     * interface.
     * 
     * @param int $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        
    }
    
    /**
     * Searches for the specified offset in the cursor.
     * 
     * @param int $offset The offset to search for.
     * @return Europa_Mongo_Document
     */
    public function offsetGet($offset)
    {
        $old = $this->getPosition();
        $ret = $this->setPosition($offset)->current();
        $this->setPosition($old);
        return $ret;
    }
    
    /**
     * Returns whether or not the specified offset exists.
     * 
     * @param int $offset The offset to check for.
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->count() > $offset;
    }
    
    /**
     * Removes the specified document from the database.
     * 
     * @param int $offset The offset to remove.
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->offsetGet($offset)->remove();
    }
    
    /**
     * Returns the connection used for this cursor.
     * 
     * @return Europa_Mongo_Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }
    
    /**
     * Goes to the specified offset.
     * 
     * @param int $offset The offset to go to.
     * @return Europa_Mongo_Cursor
     */
    public function setPosition($offset)
    {
        // if the current position is greater than the requested, rewind
        if ($offset > $this->getPosition()) {
            $this->rewind();
        }
        
        // now go to the offset
        while ($this->valid() && $this->getPosition() < $offset) {
            $this->next();
        }
        
        // set the internal position marker
        $this->_position = $offset;
        
        return $this;
    }
    
    /**
     * Returns the current offset.
     * 
     * @return int
     */
    public function getPosition()
    {
        return $this->_position;
    }
    
    /**
     * Sets which class to use for each document. If set to a false value,
     * then the default array will be returned.
     * 
     * @param string $class The class to use. Null/false/0 for none.
     * @return Europa_Mongo_Cursor
     */
    public function setClass($class)
    {
        $this->_class = (string) $class;
        return $this;
    }
    
    /**
     * Returns whether or not the cursor has been executed yet.
     * 
     * @return bool
     */
    public function isExecuted()
    {
        return $this->_isExecuted;
    }
}