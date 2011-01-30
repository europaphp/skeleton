<?php

namespace Europa\Form;

/**
 * Represents a group of form elements.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class ElementList extends Base implements Renderable, Listable
{
    /**
     * Contains the elements and element lists.
     * 
     * @var array
     */
    protected $elements = array();
    
    /**
     * Adds a valid renderable element onto the element list.
     * 
     * @param \Europa\Form\Base $element The element to add.
     * @param mixed             $offset  The offset to set the element at.
     * 
     * @return Europa_Form_ElementList
     */
    public function addElement(\Europa\Form\Base $element, $offset = null)
    {
        // append if offset is null
        if (is_null($offset)) {
            $this->elements[] = $element;
        } else {
            // automate field naming if a string is passed
            if (is_string($offset)) {
                if (!$element->id) {
                    $element->id = (string) \Europa\String::create($offset)->toProperty();
                }
                if (!$element->name) {
                    $element->name = $offset;
                }
            }
            $this->elements[$offset] = $element;
        }
        return $this;
    }
    
    /**
     * Returns the form elements applied to this list.
     * 
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }
    
    /**
     * Takes an array of values and searches for a matching value for each
     * element in the list. Recursively handles nested element lists.
     * 
     * @param array $values An array of values to search in.
     * 
     * @return \Europa\Form\ElementList
     */
    public function fill($values)
    {
        foreach ($this as $element) {
            $element->fill($values);
        }
        return $this;
    }
    
    /**
     * Converts the list to an array of name/value pairs.
     * 
     * @return array
     */
    public function toArray()
    {
        $arr = array();
        foreach ($this as $element) {
            $arr = array_merge_recursive($arr, $element->toArray());
        }
        return $arr;
    }

    /**
     * Returns a JSON object of name/value pairs.
     * 
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
    
    /**
     * Validates each elemtent.
     * 
     * @return \Europa\Form\ElementList
     */
    public function validate()
    {
        foreach ($this as $element) {
            $element->validate();
        }
        return $this;
    }
    
    /**
     * Validates each field in the list.
     * 
     * @return \Europa\Form\ElementList
     */
    public function isValid()
    {
        foreach ($this as $element) {
            if (!$element->isValid()) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Returns the messages from the invalid elements.
     * 
     * @return array
     */
    public function getMessages()
    {
        $messages = array();
        foreach ($this as $element) {
            $messages = array_merge($messages, $element->getMessages());
        }
        return $messages;
    }
    
    /**
     * Returns an element at a particular offset or null if it doesn't exist.
     * 
     * @param mixed $offset The offset to get.
     * 
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->elements[$offset];
        }
        return null;
    }
    
    /**
     * Adds an element at the given offset.
     * 
     * @param mixed $offset The offset to set the element at.
     * @param Europa_Form_Base $element The element to set.
     * 
     * @return void
     */
    public function offsetSet($offset, $element)
    {
        $this->addElement($element, $offset);
    }
    
    /**
     * Checks to see if the given offset exists.
     * 
     * @param mixed $offset The offset to check at.
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }
    
    /**
     * Removes an element at a particular offset.
     * 
     * @param mixed $offset The offset to unset.
     * 
     * @return void
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->elements[$offset]);
        }
    }
    
    /**
     * Counts the array elements.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->elements);
    }
    
    /**
     * Returns the current item.
     * 
     * @return mixed
     */
    public function current()
    {
        return current($this->elements);
    }
    
    /**
     * Returns the key of the current element.
     * 
     * @return mixed
     */
    public function key()
    {
        return key($this->elements);
    }
    
    /**
     * sets the next element in the array.
     * 
     * @return void
     */
    public function next()
    {
        next($this->elements);
    }
    
    /**
     * Rewinds the array.
     * 
     * @return void
     */
    public function rewind()
    {
        reset($this->elements);
    }

    /**
     * Returns whether or not the array can still be iterated over.
     * 
     * @return bool
     */
    public function valid()
    {
        return (bool) $this->current();
    }
}