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
     * Saving options.
     * 
     * @var array
     */
    private $_insertOptions = array();
    
    /**
     * Saving options.
     * 
     * @var array
     */
    private $_updateOptions = array();
    
    /**
     * Removing options.
     * 
     * @var array
     */
    private $_removeOptions = array();
    
    /**
     * Loads the document from the database and applies the properties
     * to the current instance.
     * 
     * @param array $criteria Any criteria to search by other than what is currently in the document.
     * @param array $fields   The fields to return and fill the document with.
     * 
     * @return Europa_Mongo_Document
     */
    public function load(array $criteria = array(), array $fields = array())
    {
        // merge criteria
        $this->fill($criteria);
        
        // make sure it's a mongo array
        $criteria = $this->toMongoArray();
        
        // if there is no criteria, do nothing
        if (!$criteria) {
            return $this;
        }
        
        // load by id
        if ($found = $this->getCollection()->findOne($criteria, $fields)) {
            $this->fill($found);
        }
        
        return $this;
    }
    
    /**
     * Saves the current document to the database. If any options are passed, they are 
     * merged with options set at a document level to be passed on every save using 
     * setSaveOptions(). Options are retrieved using getSaveOptions(). An exception is 
     * thrown if the document cannot be saved.
     * 
     * Update and insert aren't exposed to prevent ambiguity. The simple rule is that if
     * there is an "_id" then it is assumed to exist, thus an update ensues. If not, an
     * "_id" is generated and then an insert is performed. This also mitigates any issues
     * when deciding if references should be inserted or updated. In this case, they are
     * simply saved.
     * 
     * Exceptions are handled separately for updating and inserting, so if an exception
     * is thrown during saving, it will specify the attempted action.
     * 
     * Options are cascaded using in order of importance: call-time, insert/uptdate and 
     * then save.
     * 
     * @param array $options The options to use.
     * 
     * @return Europa_Mongo_Document
     */
    final public function save(array $options = array())
    {
        // if it exists and is not modified, we don't do anything
        if ($this->_id && !$this->isModified()) {
            return $this;
        }
        
        // save referenes first
        foreach ($this as $item) {
            if ($item instanceof Europa_Mongo_Document || $item instanceof Europa_Mongo_EmbeddedCollection) {
                $item->save($options);
            }
        }
        
        // update if an id exists, otherwise insert
        if ($this->_id) {
            $this->_update(array_merge($this->getSaveOptions(), $this->getUpdateOptions(), $options));
        } else {
            $this->_insert(array_merge($this->getSaveOptions(), $this->getInsertOptions(), $options));
        }
        
        return $this;
    }
    
    /**
     * Removes the current document from the database. When the item is removed, the object still
     * exists and can be manipulated, however, the "_id" is unset. This allows it to be re-saved
     * if necessary.
     * 
     * @param array $options The options to use.
     * 
     * @return Europa_Mongo_Document
     */
    final public function remove(array $options = array())
    {
        // pre-event check
        if ($this->preRemove() !== false) {
            $options = array_merge($this->getRemoveOptions(), $options);
            if (!$this->getCollection()->remove($this->toMongoArray(), $options)) {
                throw new Europa_Mongo_Exception(
                    'Could not remove ' . get_class($this) . ' from the database.'
                );
            }
            
            // unset the id
            $this->__unset('_id');
            
            // post-event
            $this->postRemove();
        }
        return $this;
    }
    
    /**
     * Sets any save options to be passed on every save.
     * 
     * @param array $options The options to use.
     * 
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
    public function getSaveOptions()
    {
        return $this->_saveOptions;
    }
    
    /**
     * Sets any insert options to be passed on every save.
     * 
     * @param array $options The options to use.
     * 
     * @return Europa_Mongo_Document
     */
    public function setInsertOptions(array $options = array())
    {
        $this->_insertOptions = $options;
        return $this;
    }
    
    /**
     * Returns the insert options that were set.
     * 
     * @return array
     */
    public function getInsertOptions()
    {
        return $this->_insertOptions;
    }
    
    /**
     * Sets any update options to be passed on every save.
     * 
     * @param array $options The options to use.
     * 
     * @return Europa_Mongo_Document
     */
    public function setUpdateOptions(array $options = array())
    {
        $this->_updateOptions = $options;
        return $this;
    }
    
    /**
     * Returns the update options that were set.
     * 
     * @return array
     */
    public function getUpdateOptions()
    {
        return $this->_updateOptions;
    }
    
    /**
     * Sets any remove options to be passed on every remove.
     * 
     * @param array $options The options to use.
     * 
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
     * Sets the collection to use for the document.
     * 
     * @param Europa_Mongo_Collection $collection The collection to use.
     * 
     * @return Europa_Mongo_Document
     */
    public function setCollection(Europa_Mongo_Collection $collection)
    {
        $this->_collection = $collection;
        return $this;
    }
    
    /**
     * Returns the collection being used. If no collection has be specifically set,
     * a default one is created.
     * 
     * @return Europa_Mongo_Collection
     */
    public function getCollection()
    {
        // if no collection link exists, attempt to auto-detect
        if (!$this->_collection) {
            $this->setCollection($this->getDefaultCollection());
        }
        return $this->_collection;
    }
    
    /**
     * Returns a default collection to use.
     * 
     * @return Europa_Mongo_Collection
     */
    protected function getDefaultCollection()
    {
        $collection = get_class($this);
        $parts      = explode('_', $collection);
        foreach ($parts as &$part) {
            $part[0] = strtolower($part[0]);
        }
        
        try {
            // create a database using a default connection
            $database   = new Europa_Mongo_Db(Europa_Mongo_Connection::getDefault(), array_shift($parts));
            $collection = implode('.', $parts);
            return new Europa_Mongo_Collection($database, $collection);
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
                . '->getDefaultCollection(), to return the desired collection. '
                . 'Message: '
                . $e->getMessage()
            );
        }
    }
    
    /**
     * Gets called prior to saving/updating/inserting. If it returns false, then saving does not continue.
     * 
     * @return mixed
     */
    protected function preSave()
    {
        
    }
    
    /**
     * Gets called after saving.
     * 
     * @return mixed
     */
    protected function postSave()
    {
        
    }
    
    /**
     * Gets called prior to inserting. If it returns false, then saving does not continue.
     * 
     * @return mixed
     */
    protected function preInsert()
    {
        
    }
    
    /**
     * Gets called after inserting.
     * 
     * @return mixed
     */
    protected function postInsert()
    {
        
    }
    
    /**
     * Gets called prior to updating. If it returns false, then saving does not continue.
     * 
     * @return mixed
     */
    protected function preUpdate()
    {
        
    }
    
    /**
     * Gets called after updating.
     * 
     * @return mixed
     */
    protected function postUpdate()
    {
        
    }
    
    /**
     * Gets called prior to removing. If it returns false, then the document is not removed.
     * 
     * @return mixed
     */
    protected function preRemove()
    {
        
    }
    
    /**
     * Gets called after removing.
     * 
     * @return mixed
     */
    protected function postRemove()
    {
        
    }
    
    /**
     * Performs an insert to the document.
     * 
     * @param array $options The options to use.
     * 
     * @return Europa_Mongo_Document
     */
    private function _insert(array $options = array())
    {
        // pre insert and pre save
        if ($this->preSave() === false || $this->preInsert() === false) {
            return $this;
        }
        
        // generate a new id and insert
        try {
            $this->_id = new MongoID;
            $this->getCollection()->insert($this->toMongoArray(), $options);
        } catch (Exception $e) {
            throw new Europa_Mongo_Exception(
                'Could not insert ' . get_class($this) . '. Message: ' . $e->getMessage()
            );
        }
        
        // do post insert and post save
        $this->postSave();
        $this->postInsert();
        
        return $this;
    }
    
    /**
     * Performs an update to the document.
     * 
     * @param array $options The options to use.
     * 
     * @return Europa_Mongo_Document
     */
    private function _update(array $options = array())
    {
        // do pre update and allow it to cancel
        if ($this->preSave() === false || $this->preUpdate() === false) {
            return $this;
        }
        
        // prepare the data
        $data         = $this->modifiers;
        $data['$set'] = $this->toMongoArray();
        unset($data['_id']);
        
        // update
        try {
            $this->getCollection()->update(array('_id' => new MongoID($this->_id)), $data, $options);
        } catch (Exception $e) {
            throw new Europa_Mongo_Exception(
                'Could not update ' . get_class($this) . '. Message: ' . $e->getMessage()
            );
        }
        
        // do post update and saving
        $this->postSave();
        $this->postUpdate();
        
        return $this;
    }
}