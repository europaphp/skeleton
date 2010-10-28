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
    private $_class = null;
    
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
    }
    
    /**
     * Calls a command using where.
     * 
     * @param string $name The name of the command.
     * @param array  $args The arguments to pass to the command.
     * 
     * @return Europa_Mongo_Collection
     */
    public function __call($name, array $args = array())
    {
        return $this->where($args[0], array('$' . $name => $args[1]));
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
     * Tells the query what fields to return. If fields is a false value,
     * then all fields will be selected. By default, all fields are
     * selected.
     * 
     * @param mixed $fields A string or array of fields to return.
     * @return Europa_Mongo_Collection
     */
    public function select($fields = null)
    {
        if (!$fields) {
            $this->_fields = array();
            return $this;
        }
        
        if (!is_array($fields)) {
            $fields = array($fields);
        }
        
        $this->_fields = array_merge($this->_fields, $fields);
        return $this;
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
            // make sure the value is a mongo id if the field is _id
            if ($field === '_id' && !$value instanceof MongoId) {
                $value = new MongoID($value);
            }
            $this->_query[$field] = $value;
        }
        return $this;
    }
    
    /**
     * Limits the results.
     * 
     * @param int $limit The limit.
     * @return Europa_Mongo_Document
     */
    public function limit($limit = null)
    {
        if (is_null($limit)) {
            return $this->_limit;
        }
        $this->_limit = (int) $limit;
        return $this->refresh();
    }
    
    /**
     * Tracks the skipping of results. Overrides the set page.
     * 
     * @param int $offset The amount to skip.
     * @return Europa_Mongo_Document
     */
    public function skip($offset = null)
    {
        if (is_null($offset)) {
            return $this->_skip;
        }
        $this->_skip = (int) $offset;
        return $this->refresh();
    }
    
    /**
     * Sets or returns the current page. If no limit is set, then
     * paging has no effect.
     * 
     * @param int $page
     * @return Europa_Mongo_Document
     */
    public function page($page = null)
    {
        $limit = $this->limit();
        $skip  = $this->skip();
        
        if (is_null($page)) {
            if (!$skip || !$limit) {
                return 1;
            }
            return $skip / $limit + 1;
        }
        
        if ($limit) {
            $this->skip($limit * $page - $limit);
        }
        
        return $this->refresh();
    }
    
    /**
     * Returns the total number of pages.
     * 
     * @return int
     */
    public function pages()
    {
        $limit = $this->limit();
        if (!$limit) {
            1;
        }
        return (int) ceil($this->total() / $limit);
    }
    
    /**
     * Counts the total in the collection with the query applied.
     * 
     * @return int
     */
    public function count()
    {
        return $this->getCursor()->count(true);
    }
    
    /**
     * Counts the total in the collection without the query applied.
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
     * @param array $query  Any additional query params to use.
     * @param array $fields Any specific fields to pass back.
     * 
     * @return Europa_Mongo_Colelction
     */
    public function execute(array $query = array(), array $fields = array())
    {
        // build the query
        $query  = array_merge($this->_query, $query);
        $fields = array_merge($this->_fields, $fields);
        
        // apply it to the cursor
        $this->_cursor = $this->find($query, $fields)->limit($this->limit())->skip($this->skip());
        
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
        $class   = $this->getClass();
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
        return $this->total() > $offset;
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
     * Sets which class to use for each document.
     * 
     * @param string $class The class to use.
     * 
     * @return Europa_Mongo_Cursor
     */
    public function setClass($class)
    {
        if (!$class || !is_string($class)) {
            throw new Europa_Mongo_Exception(
                'Specified class name must be a string.'
            );
        }
        $this->_class = $class;
        return $this;
    }
    
    /**
     * Returns the class that each document must derive from.
     * 
     * @return string
     */
    public function getClass()
    {
        if (!$this->_class) {
            $this->setClass($this->getDefaultClass());
        }
        return $this->_class;
    }
    
    /**
     * Returns the default class.
     * 
     * @return string
     */
    public function getDefaultClass()
    {
        $database   = $this->getDb()->getName();
        $collection = $this->getName();
        return ucfirst($database) . '_' . ucfirst($collection);
    }
}