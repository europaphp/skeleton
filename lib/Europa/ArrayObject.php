<?php

namespace Europa;

/**
 * Extends ArrayObject so that you are allowed to access undefined indexes/properties
 * and it will return null instead of throwing a notice.
 * 
 * @category ArrayObject
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class ArrayObject implements \IteratorAggregate, \ArrayAccess, \Serializable, \Countable
{
    /**
     * The data in the array object.
     * 
     * @var array
     */
    private $data;
    
    /**
     * Constructs a new object making sure values are passed through offsetSet.
     * 
     * @param mixed $data Any data to initialize the array object with.
     * 
     * @return \Europa\ArrayObject
     */
    public function __construct($data = array())
    {
        $this->import($data);
    }
    
    /**
     * Wraps offsetGet.
     * 
     * @param string $name The name of the property to get.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }
    
    /**
     * Wraps offsetSet.
     * 
     * @param string $name  The name of the property to set.
     * @param mixed  $value The value to set.
     * 
     * @return void
     */
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }
    
    /**
     * Wraps offsetExists.
     * 
     * @param string $name The name of the property to check for.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }
    
    /**
     * Wraps offsetUnset.
     * 
     * @param string $name The name of the property to unset.
     * 
     * @return void
     */
    public function __unset($name)
    {
        $this->offsetUnset($name);
    }
    
    /**
     * Imports data to the object.
     * 
     * @param mixed $data The data to import.
     * 
     * @return \Europa\ArrayObject
     */
    public function import($data)
    {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $k => $v) {
                $this->offsetSet($k, $v);
            }
        }
        return $this;
    }
    
    /**
     * Exports the data to a true array.
     * 
     * @return array
     */
    public function export()
    {
        $arr = array();
        foreach ($this as $index => $item) {
            if ($item instanceof ArrayObject) {
                $arr[$index] = $item->export();
            } else {
                $arr[$index] = $item;
            }
        }
        return $arr;
    }
    
    /**
     * Serializes the object into a string.
     * 
     * @return string
     */
    public function serialize()
    {
        return serialize($this->export());
    }
    
    /**
     * Unserializes the object into the array object.
     * 
     * @param string $serialized The serialized array to import.
     * 
     * @return \Europa\ArrayObject
     */
    public function unserialize($serialized)
    {
        $this->import(unserialize($serialized));
        return $this;
    }
    
    /**
     * Counts the number of elements in the first level of the array object.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }
    
    /**
     * Returns the value if it exists. If not, it returns null.
     * 
     * @param string $name The name of the value to get.
     * 
     * @return mixed
     */
    public function offsetGet($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }
    
    /**
     * Sets the specified value.
     * 
     * @param string $name The name of the value to set.
     * 
     * @return void
     */
    public function offsetSet($name, $value)
    {
        // allow auto-incrementing
        if (!$name) {
            $name = count($this);
        }
        
        // normalize sub-arrays
        if (is_array($value) || is_object($value)) {
            $class = get_class($this);
            $value = new $class($value);
        }
        
        $this->data[$name] = $value;
    }
    
    /**
     * Checks to see if the specified value exists.
     * 
     * @param string $name The name of the value to check for.
     * 
     * @return bool
     */
    public function offsetExists($name)
    {
        return isset($this->data[$name]);
    }
    
    /**
     * Unsets the specified value.
     * 
     * @param string $name The name of the value to unset.
     * 
     * @return bool
     */
    public function offsetUnset($name)
    {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }
    }
    
    /**
     * Returns the iterator for the array.
     * 
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
}