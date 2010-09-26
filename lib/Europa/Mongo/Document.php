<?php

class Europa_Mongo_Document implements Iterator, ArrayAccess, Countable
{
    /**
     * The data on in the document.
     * 
     * @var array
     */
    private $_data = array();
    
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
    
    private $_saveOptions = array();
    
    private $_removeOptions = array();
    
    /**
     * Fills the current document with the specified data.
     * 
     * @param mixed $data The data to fill the document with.
     * @return Europa_Mongo_Document
     */
    public function fill($data)
    {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $name => $value) {
                $this->set($name, $value);
            }
        }
        return $this;
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
        if ($this->preSave()) {
            $options = array_merge($this->getSaveOptions(), $options);
            if (!$this->getCollection()->save($this, $options)) {
                throw new Europa_Mongo_Exception(
                    'Could not save ' . get_class($this) . ' to the database.'
                );
            }
            $this->postSave();
        }
        return $this;
    }
    
    public function remove(array $options = array())
    {
        if ($this->preRemove()) {
            $options = array_merge($this->getRemoveOptions(), $options);
            if (!$this->getCollection()->remove($this, $options)) {
                throw new Europa_Mongo_Exception(
                    'Could not remove ' . get_class($this) . ' from the database.'
                );
            }
            $this->postRemove();
        }
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
    public function getSaveOptions()
    {
        return $this->_saveOptions;
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
     * Sets a document parameter.
     * 
     * @param string $name The name of the parameter.
     * @param mixed $value The value of the parameter.
     * @return Europa_Mongo_Document
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }
    
    public function __get($name)
    {
        return $this->get($name);
    }
    
    public function __isset($name)
    {
        return $this->has($name);
    }
    
    public function __unset($name)
    {
        return $this->clear($name);
    }
    
    public function current()
    {
        return current($this->_data);
    }
    
    public function key()
    {
        return key($this->_data);
    }
    
    public function next()
    {
        return next($this->_data);
    }
    
    public function rewind()
    {
        return reset($this->_data);
    }
    
    public function valid()
    {
        return isset($this->_data[key($this->_data)]);
    }
    
    public function offsetSet($name, $value)
    {
        return $this->set($name, $value);
    }
    
    public function offsetGet($name)
    {
        return $this->get($name);
    }
    
    public function offsetExists($name)
    {
        return $this->has($name);
    }
    
    public function offsetUnset($name)
    {
        return $this->clear($name);
    }
    
    public function count()
    {
        return count($this->_data);
    }
    
    public function set($name, $value)
    {
        $this->_data[$name] = $value;
        return $this;
    }
    
    public function get($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        return null;
    }
    
    public function has($name)
    {
        return isset($this->_data[$name]);
    }
    
    public function clear($name)
    {
        if (isset($this->_data[$name])) {
            unset($this->_data[$name]);
        }
        return $this;
    }
    
    /**
     * Sets a specific connection to use.
     * 
     * @param Europa_Mongo_Connection $connection The connection to use.
     * @return Europa_Mongo_Document
     */
    public function setConnection(Europa_Mongo_Connection $connection)
    {
        $this->_connection = $connection;
        return $this;
    }
    
    /**
     * Returns the connection to use. If no connection is set, then it
     * attempts to create one using the default settings. If a
     * connection cannot be established, then an exception is thrown.
     * 
     * @return Europa_Mongo_Connection
     */
    public function getConnection()
    {
        // if no conenction link exists, try to create a default one
        if (!$this->_connection) {
            try {
                $this->_connection = new Europa_Mongo_Connection;
            } catch (Europa_Mongo_Exception $e) {
                $class = get_class($this);
                throw new Europa_Mongo_Exception(
                    'Could not create a default connection for '
                    . $class
                    . '. Please either set a connection using '
                    . $class
                    . '->setConnection(Europa_Mongo_Connection $connection) '
                    . 'or return one by overriding '
                    . $class
                    . '->getConnection(), to return the desired connection. '
                    . 'Message: '
                    . $e->getMessage()
                );
            }
        }
        return $this->_connection;
    }
    
    /**
     * Sets the database to use.
     * 
     * @param Europa_Mongo_Db $db The database to use.
     * @return Europa_Mongo_Database
     */
    public function setDb(Europa_Mongo_Db $db)
    {
        $this->_db = $db;
        return $this;
    }
    
    /**
     * Returns the database to use. If no database is set, then it
     * attempts to create an instance of a default one.
     */
    public function getDb()
    {
        // if not database link exists, create a default one
        if (!$this->_db) {
            $parts = explode('_', get_class($this));
            $parts[0][0] = strtolower($parts[0][0]);
            try {
                $this->_db = new Europa_Mongo_Db($this->getConnection(), strtolower($parts[0]));
            } catch (Europa_Mongo_Exception $e) {
                $class = get_class($this);
                throw new Europa_Mongo_Exception(
                    'Could not create a default database for '
                    . $class
                    . '. Please either set a database using '
                    . $class
                    . '->setDb(Europa_Mongo_Db $db) '
                    . 'or return one by overriding '
                    . $class
                    . '->getDb(), to return the desired database. '
                    . 'Message: '
                    . $e->getMessage()
                );
            }
        }
        return $this->_db;
    }
    
    public function setCollection(Europa_Mongo_Collection $collection)
    {
        $this->_collection = $collection;
        return $this;
    }
    
    public function getCollection()
    {
        // if no collection link exists, create a default one
        if (!$this->_collection) {
            $collection = get_class($this);
            $parts = explode('_', $collection);
            foreach ($parts as &$part) {
                $part[0] = strtolower($part[0]);
            }
            unshift($parts[0]);
            $collection = implode('.', $parts);
            try {
                $this->_collection = new Europa_Mongo_Collection($this->getDb(), $collection);
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
}