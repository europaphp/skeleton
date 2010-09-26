<?php

class Europa_Mongo_Cursor extends MongoCursor implements ArrayAccess, Countable
{
    private $_position = 0;
    
    private $_isExecuted = false;
    
    private $_class;
    
    private $_connection;
    
    public function __construct(Europa_Mongo_Connection $connection, $collection, array $query = array(), array $fields = array())
    {
        parent::__construct($connection, $collection, $query, $fields);
        
        $this->_connection = $connection;
        $this->setClass(Europa_String::create($collection)->replace('.', '_')->toClass());
    }
    
    public function doQuery()
    {
        if (!$this->isExecuted()) {
            parent::doQuery();
            $this->rewind();
            $this->_isExecuted = true;
        }
        return $this;
    }
    
    public function current()
    {
        $this->doQuery();
        $class = $this->getClass();
        $class = new $class;
        return $class->setConnection($this->getConnection())->fill(parent::current());
    }
    
    public function next()
    {
        ++$this->_position;
        parent::next();
    }
    
    public function rewind()
    {
        $this->_position = 0;
        parent::rewind();
    }
    
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
    
    public function getPosition()
    {
        return $this->_position;
    }
    
    public function setClass($class)
    {
        $this->_class = (string) $class;
        return $this;
    }
    
    public function getClass()
    {
        return $this->_class;
    }
    
    public function isExecuted()
    {
        return $this->_isExecuted;
    }
}