<?php

/**
 * A class that represents a collection of embedded documents. Embedded
 * documents can either be normal documents or document references.
 * 
 * @category Mongo
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Mongo_EmbeddedCollection implements Europa_Mongo_Accessible
{
    /**
     * The items in the document set.
     * 
     * @var array
     */
    private $_items = array();
    
    /**
     * The class to use for the items in the collection.
     * 
     * @var string
     */
    private $_class;
    
    /**
     * Constructs a new document set and adds any documents that were passed.
     * 
     * @param string $class The class name to use for the documents.
     * @param array $documents The documents to add, if any.
     * @return Europa_Mongo_DocumentSet
     */
    public function __construct($class, $documents = array())
    {
        $this->_class = $class;
        if (is_array($documents) || is_object($documents)) {
            foreach ($documents as $offset => $document) {
                $this->offsetSet($offset, $document);
            }
        }
    }
    
    /**
     * Returns the current document.
     * 
     * @return Europa_Mongo_Document
     */
    public function current()
    {
        return current($this->_items);
    }
    
    /**
     * Returns the key of the current document.
     * 
     * @return mixed
     */
    public function key()
    {
        return key($this->_items);
    }
    
    /**
     * Moves to the next document.
     * 
     * @return Europa_Mongo_EmbeddedCollection
     */
    public function next()
    {
        next($this->_items);
        return $this;
    }
    
    /**
     * Moves to the first document.
     * 
     * @return Europa_Mongo_EmbeddedCollection
     */
    public function rewind()
    {
        reset($this->_items);
        return $this;
    }
    
    /**
     * Returns whether or not the iteration is still valid.
     * 
     * @return bool
     */
    public function valid()
    {
        return $this->current() !== false;
    }
    
    /**
     * Applies the document to the collection.
     * 
     * @param mixed $offset
     * @param mixed $document
     * 
     * @return Europa_Mongo_EmbeddedCollection
     */
    public function offsetSet($offset, $document)
    {
        // if the document isn't an instance of the specified class, make it one
        $class = $this->_class;
        if (!$document instanceof $class) {
            $document = new $class($document);
        }
        
        // default to appending if a null offset is passed
        if (!is_numeric($offset)) {
            $offset = $this->count();
        }
        
        $this->_items[$offset] = $document;
        return $this;
    }
    
    /**
     * Returns the document at the specified offset.
     * 
     * @param mixed $offset
     * 
     * @return Europa_Mongo_DocumentAbstract
     */
    public function offsetGet($offset)
    {
        if (isset($this->_items[$offset])) {
            return $this->_items[$offset];
        }
        return null;
    }
    
    /**
     * Returns whether or not the document at the specified
     * offset exists.
     * 
     * @param mixed $offset
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->_items[$offset]);
    }
    
    /**
     * Unsets the document at the specified offset if it exists.
     * 
     * @param mixed $offset
     * @return Europa_Mongo_EmbeddedCollection
     */
    public function offsetUnset($offset)
    {
        if (isset($this->_items[$offset])) {
            unset($this->_items[$offset]);
        }
        return $this;
    }
    
    /**
     * Returns the number of documents in the collection.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->_items);
    }
    
    /**
     * Saves only savable documents in the embedded collection to
     * their respective database collections.
     * 
     * @param array $options
     * @return Europa_Mongo_EmbeddedCollection
     */
    public function save(array $options = array())
    {
        foreach ($this as $document) {
            if ($document instanceof Europa_Mongo_Document) {
                $document->save($options);
            }
        }
        return $this;
    }
    
    /**
     * Removes only removable documents in the embedded collection to
     * their respective database collections and then from the
     * embedded collection.
     * 
     * @param array $options
     * @return Europa_Mongo_EmbeddedCollection
     */
    public function remove(array $options = array())
    {
        foreach ($this as $offset => $document) {
            if ($document instanceof Europa_Mongo_Document) {
                $document->remove($options);
            }
            $this->offsetUnset($offset);
        }
        return $this;
    }
    
    /**
     * Converts the collection into an array.
     * 
     * @return array
     */
    public function toArray()
    {
        $array = array();
        foreach ($this->_items as $document) {
            $array[] = $document->toArray();
        }
        return $array;
    }
    
    /**
     * Converts the collection into a MongoDB style array that
     * is passable to methods such as save/insert/find/findOne.
     * 
     * @return array
     */
    public function toMongoArray()
    {
        $array = array();
        foreach ($this->_items as $document) {
            $array[] = $document->toMongoArray();
        }
        return $array;
    }
}