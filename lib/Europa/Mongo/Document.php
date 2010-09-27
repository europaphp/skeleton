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
abstract class Europa_Mongo_Document implements Iterator, ArrayAccess, Countable
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
     * Whitelisted properties.
     * 
     * @var array
     */
    private $_whitelist = array();
    
    /**
     * Blacklisted properties.
     * 
     * @var array
     */
    private $_blacklist = array();
    
    /**
     * Contains the has one relationships.
     * 
     * @var array
     */
    private $_hasOne = array();
    
    /**
     * Contains the has many relationships.
     * 
     * @var array
     */
    private $_hasMany = array();
    
    /**
     * Property aliases.
     * 
     * @var array
     */
    private $_aliases = array();
    
    /**
     * Whether or not the document has changed.
     * 
     * @var bool
     */
    private $_modified = array();
    
    /**
     * Whether or not the current document exists in a collection.
     * 
     * @var bool
     */
    private $_exists;
    
    /**
     * Sets a document parameter.
     * 
     * @param string $name The name of the parameter.
     * @param mixed $value The value of the parameter.
     * @return Europa_Mongo_Document
     */
    final public function __set($name, $value)
    {
        return $this->set($name, $value);
    }
    
    /**
     * Returns a document parameter.
     * 
     * @param string $name The name of the parameter to get.
     * @return mixed
     */
    final public function __get($name)
    {
        return $this->get($name);
    }
    
    /**
     * Returns whether or not a particular parameter is set.
     * 
     * @param string $name The name of the parameter to check.
     * @return bool
     */
    final public function __isset($name)
    {
        return $this->has($name);
    }
    
    /**
     * Removes the specified parameter.
     * 
     * @param string $name The parameter to remove.
     * @return Europa_Mongo_Document
     */
    final public function __unset($name)
    {
        return $this->clear($name);
    }
    
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
     * @return Europa_Mongo_Document
     */
    public function load()
    {
        $criteria = array('_id' => $this->_id);
        return $this->fill($this->getCollection()->findOne($criteria));
    }
    
    /**
     * Gets called prior to inserting. If it returns false, then saving
     * does not continue.
     * 
     * @return mixed
     */
    public function preInsert()
    {
        
    }
    
    /**
     * Gets called after inserting.
     * 
     * @return mixed
     */
    public function postInsert()
    {
        
    }
    
    /**
     * Gets called prior to updating. If it returns false, then saving
     * does not continue.
     * 
     * @return mixed
     */
    public function preUpdate()
    {
        
    }
    
    /**
     * Gets called after updating.
     * 
     * @return mixed
     */
    public function postUpdate()
    {
        
    }
    
    /**
     * Gets called prior to saving/updating/inserting. If it returns
     * false, then saving does not continue.
     * 
     * @return mixed
     */
    public function preSave()
    {
    
    }
    
    /**
     * Gets called after saving.
     * 
     * @return mixed
     */
    public function postSave()
    {
    
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
        // id it's not modified we don't do anything
        if (!$this->isModified()) {
            return $this;
        }
        
        // only proceed if preSave is not false
        if ($this->preSave() !== false) {
            // trigger other pre-events
            if (!$this->exists()) {
                $this->preInsert();
            } else {
                $this->preUpdate();
            }
            
            // save the object
            $options = array_merge($this->getSaveOptions(), $options);
            if (!$this->getCollection()->save($this->toArray(), $options)) {
                throw new Europa_Mongo_Exception(
                    'Could not save ' . get_class($this) . ' to the database.'
                );
            }
            
            // trigger post-events
            $this->postSave();
            if (!$this->exists()) {
                $this->postInsert();
            } else {
                $this->postUpdate();
            }
            
            // mark as exists
            $this->_exists = true;
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
     * Gets called prior to removing. If it returns false, then the
     * document is not removed.
     * 
     * @return mixed
     */
    public function preRemove()
    {
        
    }
    
    /**
     * Gets called after removing.
     * 
     * @return mixed
     */
    public function postRemove()
    {
        
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
        if ($this->preRemove() !== false) {
            $options = array_merge($this->getRemoveOptions(), $options);
            if (!$this->getCollection()->remove(array('_id' => $this->_id), $options)) {
                throw new Europa_Mongo_Exception(
                    'Could not remove ' . get_class($this) . ' from the database.'
                );
            }
            
            // mark as not-exsting
            $this->_exists = false;
            
            // post-event
            $this->postRemove();
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
     * Returns the current parameter in the iteration.
     * 
     * @return mixed
     */
    public function current()
    {
        return current($this->_data);
    }
    
    /**
     * Returns the parameter name of the current parameter in the
     * iteration.
     * 
     * @return string
     */
    public function key()
    {
        return key($this->_data);
    }
    
    /**
     * Moves the current element to the next in the iteration.
     * 
     * @return Europa_Mongo_Document
     */
    public function next()
    {
        next($this->_data);
        return $this;
    }
    
    /**
     * Resets the internal pointer of the parameters in the iteration.
     * 
     * @return Europa_Mongo_Document
     */
    public function rewind()
    {
        reset($this->_data);
        return $this;
    }
    
    /**
     * Returns whether or not the iteration can proceed.
     * 
     * @return bool
     */
    public function valid()
    {
        return isset($this->_data[key($this->_data)]);
    }
    
    /**
     * Allows array-like setting of parameters.
     * 
     * @param string $name The name of the parmaeter to set.
     * @param mixed $value The value of the parameter to set.
     * @return Europa_Mongo_Document
     */
    public function offsetSet($name, $value)
    {
        return $this->set($name, $value);
    }
    
    /**
     * Allows array-like getting of parameters.
     * 
     * @param string $name The parameter to get.
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }
    
    /**
     * Array-like way for checking parameter existence.
     * 
     * @param string $name THe name of the parameter to check.
     * @return mixed
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }
    
    /**
     * Array-like way of removing the specified parameter.
     * 
     * @param string $name The parameter to remove.
     * @return Europa_Mongo_Document
     */
    public function offsetUnset($name)
    {
        return $this->clear($name);
    }
    
    /**
     * Counts the number of parameters in the document.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->_data);
    }
    
    /**
     * Sets the specified parameter's value.
     * 
     * @param string $name The parameter to set.
     * @param mixed $value The value to give the parameter.
     * @return Europa_Mongo_Document
     */
    public function set($name, $value)
    {
        // get real name
        $name = $this->_getPropertyFromAlias($name);
        
        // only whitelisted properties can be set
        // if there isn't a whitelist, then it can be set
        if ($this->_whitelist && !$this->_isWhitelisted($name)) {
            return $this;
        }
        
        // if the property isn't blacklisted, set it
        if ($this->_isBlacklisted($name)) {
            return $this;
        }
        
        // flag the field as modfied
        $this->_modified[] = $name;
        
        // the magic setter method
        $method = '__set' . $name;
        
        // make sure if it's the "_id" that it's a MongoId
        if ($name === '_id') {
            $value = $value instanceof MongoId ? $value : new MongoId($value);
            $this->_data['_id'] = $value;
        }
        
        // call the setter or just set the value
        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            // handle has-one and has-many relationships
            if ($this->_hasOne($name)) {
                $value = $this->_getHasOne($name, $value);
            } elseif ($this->_hasMany($name)) {
                $value = $this->_getHasMany($name, $value);
            }
            $this->_data[$name] = $value;
        }
        
        return $this;
    }
    
    /**
     * Gets the specified parameter's value.
     * 
     * @param string $name The name of the parameter to get.
     * @return mixed
     */
    public function get($name)
    {
        // get real name
        $name = $this->_getPropertyFromAlias($name);
        
        // the magic getter method
        $method = '__get' . $name;
        
        // if the method exists, just return it's value
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        
        // if the property is set, return it
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        
        return null;
    }
    
    /**
     * Returns whether or not the specified parameter exists.
     * 
     * @param string $name The parameter to check for.
     * @return bool
     */
    public function has($name)
    {
        // get real name
        $name = $this->_getPropertyFromAlias($name);
        
        return isset($this->_data[$name]);
    }
    
    /**
     * Removes the specified parameter.
     * 
     * @param string $name The parameter to remove.
     * @return bool
     */
    public function clear($name)
    {
        // get real name
        $name = $this->_getPropertyFromAlias($name);
        
        // unset only if set
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
     * 
     * @return Europa_Mongo_Db
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
        // if no collection link exists, create a default one
        if (!$this->_collection) {
            $collection = get_class($this);
            $parts = explode('_', $collection);
            foreach ($parts as &$part) {
                $part[0] = strtolower($part[0]);
            }
            array_shift($parts);
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
    
    /**
     * Sets one or more aliases for a property.
     * 
     * @param string $name The property name.
     * @param mixed $aliases A string or array of aliases.
     * @return Europa_Mongo_Document
     */
    public function alias($name, $aliases)
    {
        // make sure it's an array
        if (!isset($this->_aliases[$name])) {
            $this->_aliases[$name][] = array();
        }
        
        // normalize
        if (!is_array($aliases)) {
            $aliases = array($aliases);
        }
        
        // apply aliases
        $this->_aliases[$name][] = $alias;
        return $this;
    }
    
    /**
     * Converts the class to a mongo array that is safe for passing
     * to a mongo query.
     * 
     * @return array
     */
    public function toArray()
    {
        $array = array();
        foreach ($this->_data as $name => $value) {
            // handle has one
            if ($this->_hasOne($name)) {
                $array[$name] = new MongoId($value->_id);
                continue;
            }
            
            // handle has many
            if ($this->_hasMany($name)) {
                $array[$name] = array();
                $class = $this->_hasMany[$name];
                $class = new $class;
                foreach ($value as $ref) {
                    $array[$name] = MongoDBRef::create(
                        $class->getCollection()->getName(),
                        $ref->_id,
                        $class->getDb()->getName()
                    );
                }
                continue;
            }
            
            // handle normal values
            $array[$name] = $value;
        }
        return $array;
    }
    
    /**
     * Applies a has one relationship to the document.
     * 
     * @param string $name The name of the property.
     * @param string $class The name of the class to use.
     * @return Europa_Mongo_Document
     */
    public function hasOne($name, $class = null)
    {
        $this->_hasOne[$name] = $class ? $class : $name;
        return $this;
    }
    
    /**
     * Applies a has many relationship to the document.
     * 
     * @param string $name The name of the property.
     * @param string $class The name of the class to use.
     * @return Europa_Mongo_Document
     */
    public function hasMany($name, $class = null)
    {
        $this->_hasMany[$name] = $class ? $class : $name;
        return $this;
    }
    
    /**
     * Whitelists a particular property.
     * 
     * @param string $names The property to whitelist.
     * @return Europa_Mongo_Document
     */
    public function whitelist($names)
    {
        if (!is_array($names)) {
            $names = array($names);
        }
        foreach ($names as $name) {
            $name = $this->_getPropertyFromAlias($name);
            $this->_whitelist[] = $name;
        }
        return $this;
    }
    
    /**
     * Blacklists a particular property.
     * 
     * @param string $names The property to blacklist.
     * @return Europa_Mongo_Document
     */
    public function blacklist($names)
    {
        if (!is_array($names)) {
            $names = array($names);
        }
        foreach ($names as $name) {
            $name = $this->_getPropertyFromAlias($name);
            $this->_blacklist[] = $name;
        }
        return $this;
    }
    
    /**
     * Returns whether or not the document has changed.
     * 
     * @return bool
     */
    public function isModified($field = null)
    {
        if ($field) {
            return in_array($field, $this->_modified);
        }
        return count($this->_modified);
    }
    
    /**
     * Returns the name of the property that matches the alias. If no
     * matching alias is found, then the alias is just returned.
     * 
     * @param string $alias The alias to search for.
     * @return string
     */
    protected function _getPropertyFromAlias($alias)
    {
        foreach ($this->_aliases as $name => $aliases) {
            if (in_array($alias, $aliases)) {
                return $name;
            }
        }
        return $alias;
    }
    
    /**
     * Checks to see if a particular property is whitelisted.
     * 
     * @param string $name The name to check for.
     * @return bool
     */
    protected function _isWhitelisted($name)
    {
        $name = $this->_getPropertyFromAlias($name);
        return in_array($name, $this->_whitelist);
    }
    
    /**
     * Checks to see if a particular property is blacklisted.
     * 
     * @param string $name The name to check for.
     * @return bool
     */
    protected function _isBlacklisted($name)
    {
        $name = $this->_getPropertyFromAlias($name);
        return in_array($name, $this->_blacklist);
    }
    
    /**
     * Returns the actual document of the specified has-one.
     * 
     * @param string $name The name of the relationship.
     * @param mixed $value
     * @return Europa_Mongo_Document
     */
    protected function _getHasOne($name, MongoId $value)
    {
        $class = $this->_hasOne[$name];
        $class = new $class;
        return $class->fill($class->getCollection()->findOne(array('_id' => $value)));
    }
    
    /**
     * Returns the actual cursor of the specified has-many.
     * 
     * @param string $name The name of the relationship.
     * @param mixed $value
     * @return Europa_Mongo_Document
     */
    protected function _getHasMany($name, Europa_Mongo_Cursor $value)
    {
        $class = $this->_hasMany[$name];
        $class = new $class;
        return $class->getCollection()->find(array('_id' => array('$in' => array())));
    }
    
    /**
     * Returns whether or not the specified has-one relationship exists.
     * 
     * @param string $name The relationship name.
     * @return bool
     */
    protected function _hasOne($name)
    {
        return in_array($name, $this->_hasOne);
    }
    
    /**
     * Returns whether or not the specified has-many relationship exists.
     * 
     * @param string $name The relationship name.
     * @return bool
     */
    protected function _hasMany($name)
    {
        return in_array($name, $this->_hasMany);
    }
}