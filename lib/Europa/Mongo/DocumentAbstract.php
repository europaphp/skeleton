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
     * @param object|array $params An iterable element containing to set.
     * 
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
     * @param string $name  The name of the parameter.
     * @param mixed  $value The value of the parameter.
     * 
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
     * 
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
     * 
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
     * 
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
     * 
     * @return Europa_Mongo_Document
     */
    public function fill($data)
    {
        if (!$data) {
            return $this;
        }
        
        if ($this->isId($data)) {
            $this->setId($data);
            return $this;
        }
        
        if ($this->isRef($data)) {
            $this->setRef($data);
            return $this;
        }
        
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
     * Returns the parameter name of the current parameter in the iteration.
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
     * @param string $name  The name of the parmaeter to set.
     * @param mixed  $value The value of the parameter to set.
     * 
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
     * 
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
     * 
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
     * 
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
     * Checks to make sure the passed $id is a valid MongoId.
     * 
     * @param mixed $id The id to check.
     * 
     * @return bool
     */
    public function isId($id)
    {
        if ($id instanceof MongoId) {
            return true;
        }
        $new = new MongoId($id);
        return $id === $new->__toString();
    }
    
    /**
     * Returns whether or not the specified $ref is a MongoDBRef.
     * 
     * @param mixed $ref The ref to check.
     * 
     * @return bool
     */
    public function isRef($ref)
    {
        return MongoDBRef::isRef($ref);
    }
    
    /**
     * Formats the passed id and configures the object appropriately.
     * 
     * @param mixed $id The id to set.
     * 
     * @return Europa_Mongo_DocumentAbstract
     */
    public function setId($id)
    {
        $this->_data['_id'] = new MongoId((string) $id);
        return $this;
    }
    
    /**
     * Sets the specified database reference. It automatically selects the
     * correct database (if specified) and collection while setting the $id.
     * 
     * @param array $ref The reference to set.
     * 
     * @return Europa_Mongo_DocumentAbstract
     */
    public function setRef(array $ref)
    {
        if (isset($ref['$db'])) {
            $this->setDb($ref['$db']);
        }
        $this->setCollection($ref['$ref']);
        $this->setId($ref['$id']);
        return $this;
    }
    
    /**
     * Sets the specified parameter's value.
     * 
     * @param string $name  The parameter to set.
     * @param mixed  $value The value to give the parameter.
     * 
     * @return Europa_Mongo_Document
     */
    public function set($name, $value)
    {
        // get real name
        $name = $this->getPropertyFromAlais($name);
        
        // id and dbref automation
        if ($name === '_id') {
            return $this->setId($value);
        } elseif (isset($this->_hasOne[$name])) {
            $class = $this->_hasOne[$name];
            $value = new $class($value);
        } elseif (isset($this->_hasMany[$name])) {
            $value = new Europa_Mongo_EmbeddedCollection($this->_hasMany[$name], $value);
        }
        
        // set the value
        $this->_data[$name] = $value;
        
        // chain
        return $this;
    }
    
    /**
     * Gets the specified parameter's value.
     * 
     * @param string $name The name of the parameter to get.
     * 
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
            $this->_data[$name] = new Europa_Mongo_EmbeddedCollection($this->_hasMany[$name]);
            return $this->_data[$name];
        }
        
        return null;
    }
    
    /**
     * Returns whether or not the specified parameter exists.
     * 
     * @param string $name The parameter to check for.
     * 
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
     * 
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
            if ($item instanceof MongoId) {
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
            // handle has one
            if (isset($this->_hasOne[$name])) {
                if ($item instanceof Europa_Mongo_EmbeddedDocument) {
                    $array[$name] = $item->toMongoArray();
                } elseif ($item instanceof Europa_Mongo_Document) {
                    $array[$name] = $item->_id;
                }
                continue;
            }
            
            // handle has many
            if (isset($this->_hasMany[$name])) {
                $array[$name] = array();
                foreach ($item as $subItem) {
                    if ($subItem instanceof Europa_Mongo_EmbeddedDocument) {
                        $array[$name][] = $subItem->toMongoArray();
                    } elseif ($item instanceof Europa_Mongo_Document) {
                        $array[$name][] = $item->_id;
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
     * @param string $name  The name of the property.
     * @param string $class The name of the class to use.
     * 
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
     * @param string $name  The name of the property.
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