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
     * Constnat for sort ascending.
     * 
     * @var int
     */
    const SORT_ASC = 1;
    
    /**
     * Constant for sort descending.
     * 
     * @var int
     */
    const SORT_DESC = -1;
    
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
     * Limits the results. The limit is tracked to provide paging information.
     * 
     * @param int $limit The limit.
     * @return Europa_Mongo_Cursor
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
     * @return Europa_Mongo_Cursor
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
     * Returnst the starting offset.
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
            parent::limit($this->getLimit());
            parent::skip($this->getStartoffset());
        }
        return parent::count($foundOnly);
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
        if (!$this->isExecuted()) {
            $this->rewind();
        }
        
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
     * Overridden to reset the internal position tracker and process limits and offsets
     * before the iteration starts and cursor is executed. This also sets the interal
     * execution flag as to tell if the cursor has been executed yet.
     * 
     * @return void
     */
    public function rewind()
    {
        if (!$this->isExecuted()) {
            parent::limit($this->getLimit());
            parent::skip($this->getStartOffset());
            $this->_isExecuted = true;
        }
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