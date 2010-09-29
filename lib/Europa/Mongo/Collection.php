<?php

/**
 * The main collection class used for MongoDB top-level collection manipulation.
 * 
 * @category Mongo
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Mongo_Collection extends MongoCollection implements Europa_Mongo_Accessible
{
    /**
     * The database to use for the collection.
     * 
     * @var Europa_Mongo_Db
     */
    private $_db;
    
    /**
     * The name of the collection.
     * 
     * @var string
     */
    private $_name;
    
    /**
     * The query to send to MongoDB.
     * 
     * @var array
     */
    private $_query = array();
    
    /**
     * The last query that was run.
     * 
     * @var array
     */
    private $_lastQuery = array();
    
    /**
     * The fields to return.
     * 
     * @var array
     */
    private $_fields = array();
    
    /**
     * The cursor returned from the query.
     * 
     * @var Europa_Mongo_Cursor|null
     */
    private $_cursor = null;
    
    /**
     * The position of the cursor.
     * 
     * @var int
     */
    private $_position = 0;
    
    /**
     * The class name of the document object to instantiate for every document.
     * 
     * @var string
     */
    private $_class;
    
    /**
     * The result limit.
     * 
     * @var int|null
     */
    private $_limit = null;
    
    /**
     * The results to skip.
     * 
     * @var int|null
     */
    private $_skip = null;
    
    /**
     * The current page of results.
     * 
     * @var int|null
     */
    private $_page = null;
    
    /**
     * Constructs a new collection and sets defaults.
     * 
     * @param Europa_Mongo_Db $db
     * @param string $name
     * @return Europa_Mongo_Collection
     */
    public function __construct(Europa_Mongo_Db $db, $name)
    {
        parent::__construct($db, $name);
        $this->_db   = $db;
        $this->_name = $name;
        $this->setClass(Europa_String::create($db->getName() . '.' . $name)->replace('.', '_')->toClass());
    }
    
    /**
     * Adds a claus for the specified field.
     * 
     * @param string $field
      *@param mixed $value
     * @return Europa_Mongo_Collection
     */
    public function where($field, $value)
    {
        // if the value is an array then it's an operator
        if (is_array($value)) {
            // make sure the value is an array, or it's overwritten
            if (!isset($this->_query[$field]) || !is_array($this->_query[$field])) {
                $this->_query[$field] = array();
            }
            
            // apply each operator
            foreach ($value as $k => $v) {
                $this->_query[$field][$k] = $v;
            }
        // handle straight values
        } else {
            $this->_query[$field] = $value;
        }
        return $this;
    }
    
    /**
     * Returns the db instance to use.
     * 
     * @return Europa_Mongo_Db
     */
    final public function getDb()
    {
        return $this->_db;
    }
    
    /**
     * Returns the name of the collection.
     * 
     * @return string
     */
    final public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Limits the results. The limit is tracked to provide paging information.
     * 
     * @param int $limit The limit.
     * @return Europa_Mongo_Document
     */
    public function limit($limit)
    {
        $this->_limit = is_null($limit) ? $limit : (int) $limit;
        return $this;
    }
    
    /**
     * Tracks the skipping of results. Overrides the set page.
     * 
     * @param int $offset The amount to skip.
     * @return Europa_Mongo_Document
     */
    public function skip($offset)
    {
        $this->_page = null;
        $this->_skip = is_null($offset) ? $offset : (int) $offset;
        return $this;
    }
    
    /**
     * Sets the page to return. Overrides the set skip.
     * 
     * @param int $page
     * @return Europa_Mongo_Document
     */
    public function page($page)
    {
        $this->_page = is_null($page) ? $page : (int) $page;
        $this->_skip = null;
        return $this;
    }
    
    /**
     * Returns the set limit. If no limit is set, then it is equal
     * to the total number of results.
     * 
     * @return int
     */
    public function getLimit()
    {
        if (is_null($this->_limit)) {
            return $this->count();
        }
        return $this->_limit;
    }
    
    /**
     * Returns the starting offset.
     * 
     * @return int
     */
    public function getStartOffset()
    {
        if (!is_null($this->_skip)) {
            return $this->_skip;
        }
        
        if (!is_null($this->_page)) {
            $limit = $this->getLimit();
            return $this->_page * $limit - $limit;
        }
        
        return 0;
    }
    
    /**
     * Returns the ending offset.
     * 
     * @return int
     */
    public function getEndOffset()
    {
        $start = $this->getStartOffset();
        $limit = $this->getLimit();
        $count = $this->count();
        return $limit < $count ? $limit : $count - $start;
    }
    
    /**
     * Returns the current page number.
     * 
     * @return int
     */
    public function getPage()
    {
        $limit = $this->getLimit();
        $start = $this->getStartOffset();
        if (!$limit || !$start) {
            return 1;
        }
        return $limit > $start ? ceil($limit / $start) + 1 : ceil($start / $limit) + 1;
    }
    
    /**
     * Returns the total number of pages.
     * 
     * @return int
     */
    public function getTotalPages()
    {
        return ceil($this->count() / $this->getLimit());
    }
    
    /**
     * Returns the starting page up to the specified range. If you are on page 3
     * and specify 5, then 1 will be returned. If you are on page 10 and specify
     * 5, 5 will be returned.
     * 
     * @param int $range
     * @return int
     */
    public function getStartPage($range)
    {
        $page = $this->getPage();
        if ($range >= $page) {
            return 1;
        }
        return $page - $range;
    }
    
    /**
     * Returns the ending page up to the specified range. If you are on page 3
     * and specify 5, then 8 will be returned. If you specify the same settings
     * but only have a total of 6 pages, then 6 will be returned.
     * 
     * @param int $range
     * @return int
     */
    public function getEndPage($range)
    {
        $sum   = $this->getPage() + $range;
        $total = $this->getTotalPages();
        if ($sum >= $total) {
            return $total;
        }
        return $sum;
    }
    
    /**
     * Overridden to provide implementation for overridden limiting and
     * skipping.
     * 
     * @param bool $foundOnly Whether or not to send limit/skip info to count.
     * @return int
     */
    public function count($foundOnly = false)
    {
        if ($foundOnly) {
            $this->getCursor()->limit($this->getLimit());
            $this->getCursor()->skip($this->getStartoffset());
        }
        return $this->getCursor()->count($foundOnly);
    }
    
    /**
     * Turns the collection into an array.
     * 
     * @return array
     */
    public function toArray()
    {
        $array = array();
        foreach ($this as $value) {
            $array[] = $value->toArray();
        }
        return $array;
    }
    
    /**
     * Turns the collection into a mongo-safe array.
     * 
     * @return array
     */
    public function toMongoArray()
    {
        $array = array();
        foreach ($this as $value) {
            $array[] = $value->toMongoArray();
        }
        return $array;
    }
    
    public function execute(array $query = array(), array $fields = array())
    {
        // build the query
        $query = array_merge($this->_lastQuery, $this->_query, $query);
        
        // set the last query as to re-use any settings
        $this->_lastQuery = $query;
        
        // apply it to the cursor
        $this->_cursor = $this->find($this->_query, $this->_fields);
        
        return $this;
    }
    
    /**
     * Returns the cursor. If it hasn't been executed yet, it is executed.
     * 
     * @return Europa_Mongo_Cursor
     */
    final public function getCursor()
    {
        if (!$this->_cursor) {
            $this->execute();
        }
        return $this->_cursor;
    }
    
    /**
     * Returns the current item.
     * 
     * @return Europa_Mongo_Document
     */
    final public function current()
    {
        // get the current element
        $current = $this->getCursor()->current();
        
        // if a class is specified, instantiate it, fill it and return it
        $class = $this->_class;
        $class = new $class;
        return $class->setConnection($this->getDb()->getConnection())->fill($current);
    }
    
    /**
     * Returns the current item's _id.
     * 
     * @return string
     */
    final public function key()
    {
        return $this->getCursor()->key();
    }
    
    /**
     * Moves to the next item in the iteration.
     * 
     * @return mixed
     */
    final public function next()
    {
        ++$this->_position;
        $this->getCursor()->next();
    }
    
    /**
     * Resets the iteration.
     * 
     * @return mixed
     */
    final public function rewind()
    {
        $this->_position = 0;
        $this->getCursor()->rewind();
    }
    
    /**
     * Returns whether or not the iteration is still valued.
     * 
     * @return bool
     */
    final public function valid()
    {
        return $this->getCursor()->valid();
    }
    
    /**
     * Sets the specified item.
     * 
     * @param mixed $offset
     * @param mixed $value
     * @return Europa_Mongo_Document
     */
    final public function offsetSet($offset, $document)
    {
        if (!$document instanceof Europa_Mongo_MainDocument) {
            throw new Europa_Mongo_Exception(
                'Only instances of Europa_Mongo_MainDocument can be applied to a Europa_Mongo_Collection.'
            );
        }
        $document->setCollection($this)->save();
        return $this->execute();
    }
    
    /**
     * Returns the specified item.
     * 
     * @param mixed $offset
     * @return Europa_Mongo_Document
     */
    final public function offsetGet($offset)
    {
        $old = $this->getPosition();
        $ret = $this->setPosition($offset)->current();
        $this->setPosition($old);
        return $ret;
    }
    
    /**
     * Returns whether or not the specified item exists.
     * 
     * @param mixed $offset
     * @return Europa_Mongo_Document
     */
    final public function offsetExists($offset)
    {
        return $this->count() > $offset;
    }
    
    /**
     * Unsets the specified item.
     * 
     * @param mixed $offset
     * @return Europa_Mongo_Document
     */
    final public function offsetUnset($offset)
    {
        $this->offsetGet($offset)->remove();
        return $this;
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
}