<?php

/**
 * The main document class used for MongoDB top-level document manipulation.
 * 
 * @category Mongo
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
abstract class Europa_Mongo_Document extends Europa_Mongo_DocumentAbstract
{
    /**
     * The MongoDB connection that is being used.
     * 
     * @var Europa_Mongo_Connection
     */
    private $_connection;
    
    /**
     * The MongoDB database that is being used.
     * 
     * @var Europa_Mongo_Db
     */
    private $_db;
    
    /**
     * The MongoDB collection that is being used.
     * 
     * @var Europa_Mongo_Collection
     */
    private $_collection;
    
    /**
     * Saving options.
     * 
     * @var array
     */
    private $_saveOptions = array();
    
    /**
     * Removing options.
     * 
     * @var array
     */
    private $_removeOptions = array();
    
    /**
     * Whether or not the current document exists in a collection.
     * 
     * @var bool
     */
    private $_exists;
    
    /**
     * Returns whether or not the specified record exists.
     * 
     * @return bool
     */
    public function exists()
    {
        if (is_null($this->_exists)) {
            if ($this->getCollection()->findOne(array('_id' => $this->_id))) {
                $this->_exists = true;
            } else {
                $this->_exists = false;
            }
        }
        return $this->_exists;
    }
    
    /**
     * Loads the document from the database and applies the properties
     * to the current instance.
     * 
     * @param array $criteria Any criteria to search by other than what
     * is currently in the document.
     * @param array $fields The fields to return and fill the document
     * with.
     * @return Europa_Mongo_Document
     */
    public function load(array $criteria = array(), array $fields = array())
    {
        // merge criteria
        $this->fill($criteria);
        
        // make sure it's a mongo array
        $criteria = $this->toArray();
        
        // if there is no criteria, do nothing
        if (!$criteria) {
            return $this;
        }
        
        // load by id
        return $this->fill($this->getCollection()->findOne($criteria, $fields));
    }
    
    /**
     * Saves the current document to the database. If any options
     * are passed, they are merged with options set at a document
     * level to be passed on every save using setSaveOptions().
     * Options are retrieved using getSaveOptions(). An exception
     * is thrown if the document cannot be saved.
     * 
     * @param array $options The options to use.
     * @return Europa_Mongo_Document
     */
    public function save(array $options = array())
    {
        // if it exists and is not modified we don't do anything
        if ($this->exists() && !$this->isModified()) {
            var_dump('arg');
            return $this;
        }
        
        // only proceed if preSave is not false
        if ($this->_preSave() !== false) {
            $options = array_merge($this->getSaveOptions(), $options, array('upsert' => true));
            
            // set a default id if it doesn't exist
            if (!$this->_id) {
                $this->_id = new MongoId;
            }
            
            // save referenes first
            foreach ($this as $item) {
                if (
                    $item instanceof Europa_Mongo_Document
                    || $item instanceof Europa_Mongo_EmbeddedCollection
                ) {
                    $item->save($options);
                }
            }
            
            try {
                $this->getCollection()->update(array('_id' => $this->_id), $this->toMongoArray(), $options);
            } catch (Exception $e) {
                throw new Europa_Mongo_Exception(
                    'Could not save ' . get_class($this) . ' to the database with message: '
                    . $e->getMessage()
                );
            }
            
            // mark as exists
            $this->_exists = true;
            
            // trigger post-events
            $this->_postSave();
        }
        
        // chain
        return $this;
    }
    
    /**
     * Sets any save options to be passed on every save.
     * 
     * @param array $options The options to use.
     * @return Europa_Mongo_Document
     */
    public function setSaveOptions(array $options = array())
    {
        $this->_saveOptions = $options;
        return $this;
    }
    
    /**
     * Returns the save options that were set.
     * 
     * @return array
     */
    protected function getSaveOptions()
    {
        return $this->_saveOptions;
    }
    
    /**
     * Removes the current document from the database.
     * 
     * @param array $options The options to use.
     * @return Europa_Mongo_Document
     */
    public function remove(array $options = array())
    {
        // pre-event check
        if ($this->_preRemove() !== false) {
            $options = array_merge($this->getRemoveOptions(), $options);
            if (!$this->getCollection()->remove($this->toMongoArray(), $options)) {
                throw new Europa_Mongo_Exception(
                    'Could not remove ' . get_class($this) . ' from the database.'
                );
            }
            
            // mark as not-exsting
            $this->_exists = false;
            
            // post-event
            $this->_postRemove();
        }
        return $this;
    }
    
    /**
     * Sets any remove options to be passed on every remove.
     * 
     * @param array $options The options to use.
     * @return Europa_Mongo_Document
     */
    public function setRemoveOptions(array $options = array())
    {
        $this->_removeOptions = $options;
        return $this;
    }
    
    /**
     * Returns the remove options that were set.
     * 
     * @return array
     */
    public function getRemoveOptions()
    {
        return $this->_removeOptions;
    }
    
    /**
     * Sets the collection to use.
     * 
     * @param Europa_Mongo_Collection $collection The collection to use.
     * @return Europa_Mongo_Document
     */
    public function setCollection(Europa_Mongo_Collection $collection)
    {
        $this->_collection = $collection;
        return $this;
    }
    
    /**
     * Returns the collection being used.
     * 
     * @return Europa_Mongo_Collection
     */
    public function getCollection()
    {
        // if no collection link exists, attempt to auto-detect
        if (!$this->_collection) {
            $collection = get_class($this);
            $parts = explode('_', $collection);
            foreach ($parts as &$part) {
                $part[0] = strtolower($part[0]);
            }
            
            $connection = Europa_Mongo_Connection::getDefault();
            $connection = $connection ? $connection : new Europa_Mongo_Connection;
            $database   = new Europa_Mongo_Db($connection, array_shift($parts));
            $collection = implode('.', $parts);
            
            try {
                $this->_collection = new Europa_Mongo_Collection($database, $collection);
            } catch (Europa_Mongo_Exception $e) {
                $class = get_class($this);
                throw new Europa_Mongo_Exception(
                    'Could not create a default collection for '
                    . $class
                    . '. Please either set a collection using '
                    . $class
                    . '->setCollection(Europa_Mongo_Collection $collection) '
                    . 'or return one by overriding '
                    . $class
                    . '->getCollection(), to return the desired collection. '
                    . 'Message: '
                    . $e->getMessage()
                );
            }
        }
        return $this->_collection;
    }
    
    /**
     * Gets called prior to saving/updating/inserting. If it returns
     * false, then saving does not continue.
     * 
     * @return mixed
     */
    protected function _preSave()
    {
        
    }
    
    /**
     * Gets called after saving.
     * 
     * @return mixed
     */
    protected function _postSave()
    {
        
    }
    
    /**
     * Gets called prior to removing. If it returns false, then the
     * document is not removed.
     * 
     * @return mixed
     */
    protected function _preRemove()
    {
        
    }
    
    /**
     * Gets called after removing.
     * 
     * @return mixed
     */
    protected function _postRemove()
    {
        
    }
}