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
    private $_limit = 0;
    
    /**
     * The results to skip.
     * 
     * @var int|null
     */
    private $_skip = 0;
    
    /**
     * The current page of results.
     * 
     * @var int|null
     */
    private $_page = 0;
    
    /**
     * Whether or not to re-execute the query when accessed.
     * 
     * @var bool
     */
    private $_refresh = false;
    
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
        $this->refresh();
        
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
        $this->_limit = (int) $limit;
        return $this->refresh();
    }
    
    /**
     * Tracks the skipping of results. Overrides the set page.
     * 
     * @param int $offset The amount to skip.
     * @return Europa_Mongo_Document
     */
    public function skip($offset)
    {
        $this->_page = 0;
        $this->_skip = (int) $offset;
        return $this->refresh();
    }
    
    /**
     * Sets the page to return. Overrides the set skip.
     * 
     * @param int $page
     * @return Europa_Mongo_Document
     */
    public function page($page)
    {
        $this->_page = (int) $page;
        $this->_skip = 0;
        return $this->refresh();
    }
    
    /**
     * Returns the set limit. If no limit is set, then it is equal
     * to the total number of results.
     * 
     * @return int
     */
    public function getLimit()
    {
        if ($this->_limit === 0) {
            return parent::count();
        }
        return $this->_limit;
    }
    
    /**
     * Returns the offset no matter of skip or page was set.
     * 
     * @return int
     */
    public function getOffset()
    {
        if ($this->_skip > 0) {
            return $this->_skip;
        }
        
        if ($this->_page > 0) {
            $limit = $this->getLimit();
            return $this->_page * $limit - $limit;
        }
        
        return 0;
    }
    
    /**
     * Returns the starting offset. Generally, this is 1
     * greater than the return value of getOffset.
     * 
     * @return int
     */
    public function getStartOffset()
    {
        return $this->getOffset() + 1;
    }
    
    /**
     * Returns the ending offset. This is generally the
     * limit amount on top of the starting offset up to
     * the total number of results.
     * 
     * @return int
     */
    public function getEndOffset()
    {
        $start  = $this->getOffset();
        $limit  = $this->getLimit();
        $count  = $this->total();
        $offset = $start + $limit;
        return $offset < $count ? $offset : $count;
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
        return (int) ($limit > $start ? ceil($limit / $start) : ceil($start / $limit));
    }
    
    /**
     * Returns the total number of pages.
     * 
     * @return int
     */
    public function getTotalPages()
    {
        $limit  = $this->getLimit();
        $total  = $this->total();
        $offset = $this->getOffset();
        
        if ($limit) {
            return (int) ceil($total / $limit);
        }
        
        if ($offset) {
            return 2;
        }
        
        return 1;
    }
    
    /**
     * Returns the starting page up to the specified range. If you are on page 3
     * and specify 5, then 1 will be returned. If you are on page 10 and specify
     * 5, 5 will be returned.
     * 
     * @param int $range
     * @return int
     */
    public function getStartPage($range = 0)
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
    public function getEndPage($range = 0)
    {
        $sum   = $this->getPage() + $range;
        $total = $this->getTotalPages();
        if ($sum >= $total) {
            return $total;
        }
        return $sum;
    }
    
    /**
     * Counts the current number of items in the iteration.
     * 
     * @return int
     */
    public function count()
    {
        return $this->getCursor()->count(true);
    }
    
    /**
     * Counts the total in the collection with the query applied.
     */
    public function total()
    {
        return $this->getCursor()->count(false);
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
    
    /**
     * Executes the current query.
     * 
     * @param array $query
     * @param array $fields
     * @return Europa_Mongo_Colelction
     */
    public function execute(array $query = array(), array $fields = array())
    {
        // build the query
        $query  = array_merge($this->_query, $query);
        $fields = array_merge($this->_fields, $fields);
        
        // apply it to the cursor
        $this->_cursor = $this->find($query, $fields)->limit($this->getLimit())->skip($this->getOffset());
        
        // rewind to make sure the cursor is reset
        $this->_cursor->rewind();
        
        // set the last query as to re-use any settings
        $this->_lastQuery = $query;
        
        // mark as refreshed and return the collection
        return $this->stop();
    }
    
    /**
     * Returns the cursor. If it hasn't been executed yet, it is executed.
     * 
     * @return Europa_Mongo_Cursor
     */
    final public function getCursor()
    {
        if (!$this->_cursor || $this->hasPendingRefresh()) {
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
        $current = $this->getCursor()->current();
        $class   = $this->_class;
        $class   = new $class;
        return $class->setCollection($this)->fill($current);
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
        $class = $this->getClass();
        if (!$document instanceof $class) {
            throw new Europa_Mongo_Exception(
                'Only instances of ' . $this->getClass() . ' can be applied to collection ' . $this->getName() . '.'
            );
        }
        $document->setCollection($this)->save();
        return $this->refresh();
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
        return $this->refresh();
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
     * Sets the refresh flag to true.
     * 
     * @return Europa_Mongo_Collection
     */
    public function refresh()
    {
        $this->_refresh = true;
        return $this;
    }
    
    /**
     * Sets the refresh flag to false.
     * 
     * @return Europa_Mongo_Collection
     */
    public function stop()
    {
        $this->_refresh = false;
        return $this;
    }
    
    /**
     * Whether or not the refresh flag is set to true.
     * 
     * @return bool
     */
    public function hasPendingRefresh()
    {
        return $this->_refresh;
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
     * Returns the class that each document must derive from.
     * 
     * @return string
     */
    public function getClass()
    {
        return $this->_class;
    }
}