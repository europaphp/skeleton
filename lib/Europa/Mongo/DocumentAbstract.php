<?php

/**
 * The base document class defining base methods for all types of documents.
 * 
 * @category Mongo
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
abstract class Europa_Mongo_DocumentAbstract implements Europa_Mongo_Accessible
{
    /**
     * Any modifiers applied to the fields in this document.
     * 
     * @var array
     */
    protected $modifiers = array();
    
    /**
     * The data on in the document.
     * 
     * @var array
     */
    private $_data = array();
    
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
     * Property aliases.
     * 
     * @var array
     */
    private $_aliases = array();
    
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
     * Constructs a new document and sets any passed params.
     * 
     * @param object|array $params An iterable element containing params
     * to set.
     * @return Europa_Mongo_Document
     */
    public function __construct($params = array())
    {
        $this->preConstruct();
        $this->fill($params);
        $this->postConstruct();
    }
    
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
     * Assumes a modifier is being called.
     * 
     * @param string $name The name of the modifier.
     * @param array  $args The arguments being sent to the modifier.
     * 
     * @return Europa_Mongo_DocumentAbstract
     */
    final public function __call($name, array $args = array())
    {
        // handle a single modifier or multiple modifiers
        if (!is_array($args[0])) {
            $args[0] = array($args[0] => $args[1]);
        }
        return $this->setModifier($name, $args[0]);
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
        $name = $this->getPropertyFromAlais($name);
        
        // force has-one to be a Europa_Mongo_Document
        if (isset($this->_hasOne[$name])) {
            $class = $this->_hasOne[$name];
            $value = new $class($value);
        } elseif (isset($this->_hasMany[$name])) {
            $class = $this->_hasMany[$name];
            $value = new Europa_Mongo_EmbeddedCollection($class, $value);
        }
        
        // set the value
        $this->_data[$name] = $value;
        
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
        $name = $this->getPropertyFromAlais($name);
        
        // if the value exists, return it
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        
        // handle singular relations
        if (isset($this->_hasOne[$name])) {
            $class = $this->_hasOne[$name];
            $class = new $class;
            $this->_data[$name] = $class;
            return $class;
        }
        
        // handle multiple relations
        if (isset($this->_hasMany[$name])) {
            $class = new Europa_Mongo_EmbeddedCollection($this->_hasMany[$name]);
            $this->_data[$name] = $class;
            return $class;
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
        $name = $this->getPropertyFromAlais($name);
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
        $name = $this->getPropertyFromAlais($name);
        
        // unset only if set
        if (isset($this->_data[$name])) {
            unset($this->_data[$name]);
        }
        
        // unset it from the document
        $this->setModifier('unset', array($name => 1));
        
        return $this;
    }
    
    /**
     * Applies a modifier to the particular field.
     * 
     * @param string $modifier The modifier to apply.
     * @param array  $args     The arguments provided.
     * 
     * @return Europa_Mongo_Document
     */
    public function setModifier($modifier, array $args = array())
    {
        $modifier = '$' . $modifier;
        
        if (!isset($this->modifiers[$modifier])) {
            $this->modifiers[$modifier] = array();
        }
        
        foreach ($args as $name => $value) {
            // see if it's accesible, if so, make it a mongo array
            if ($value instanceof Europa_Mongo_Accessible) {
                $value = $value->toMongoArray();
            }
            
            // add the modifier
            $this->modifiers[$modifier][$name] = $value;
        }
        
        return $this;
    }
    
    /**
     * Sets one or more aliases for a property.
     * 
     * @param string $name  The property name.
     * @param string $alias The property alias.
     * 
     * @return Europa_Mongo_Document
     */
    public function alias($name, $alias)
    {
        // set the alias
        $this->_aliases[$alias] = $name;
        
        // chain
        return $this;
    }
    
    /**
     * Returns a raw PHP array of the data in the document.
     * 
     * @return array
     */
    public function toArray()
    {
        $array = array();
        foreach ($this->_data as $name => $item) {
            if ($item instanceof MongoId || $item instanceof MongoDBRef) {
                $item = (string) $item;
            } elseif ($item instanceof Europa_Mongo_Accessible) {
                $item = $item->toArray();
            }
            $array[$name] = $item;
        }
        return $array;
    }
    
    /**
     * Converts the class to a mongo array that is safe for passing
     * to a mongo query.
     * 
     * @return array
     */
    public function toMongoArray()
    {
        // the prepared array
        $array = array();
        
        // sift through the data
        foreach ($this->_data as $name => $item) {
            // force a mongo id to be an instance of MongoId
            if ($name === '_id') {
                $array[$name] = new MongoId((string) $item);
                continue;
            }
            
            // handle has one
            if (isset($this->_hasOne[$name])) {
                if ($item instanceof Europa_Mongo_EmbeddedDocument) {
                    $array[$name] = $item->toMongoArray();
                } else {
                    $array[$name] = new MongoId((string) $item->_id);
                }
                continue;
            }
            
            // handle has many
            if (isset($this->_hasMany[$name])) {
                $array[$name] = array();
                foreach ($item as $subItem) {
                    if ($subItem instanceof Europa_Mongo_EmbeddedDocument) {
                        $array[$name][] = $subItem->toMongoArray();
                    } else {
                        $array[$name][] = new MongoId((string) $subItem->_id);
                    }
                }
                continue;
            }
            
            // handle normal values
            $array[$name] = $item;
        }
        
        // return the prepared array
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
     * 
     * @return Europa_Mongo_Document
     */
    public function hasMany($name, $class = null)
    {
        $this->_hasMany[$name] = $class ? $class : $name;
        return $this;
    }
    
    /**
     * Returns the name of the property that matches the alias. If no
     * matching alias is found, then the alias is just returned.
     * 
     * @param string $alias The alias to search for.
     * 
     * @return string
     */
    protected function getPropertyFromAlais($alias)
    {
        if (isset($this->_aliases[$alias])) {
            return $this->_aliases[$alias];
        }
        return $alias;
    }
    
    /**
     * Provides an easy way to hook into pre-construction.
     * 
     * @return mixed
     */
    protected function preConstruct()
    {
        
    }
    
    /**
     * Provides an easy way to hook into post-construction.
     * 
     * @return mixed
     */
    protected function postConstruct()
    {
        
    }
}