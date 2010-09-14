<?php

interface Europa_Form_Listable extends ArrayAccess, Iterator
{
    /**
     * Returns the form elements applied to this list.
     * 
     * @return array
     */
    public function getElements();
    
    /**
     * Takes an array of values and searches for a matching value for each
     * element in the list. Recursively handles nested element lists.
     * 
     * @param array $values An array of values to search in.
     * @return Europa_Form_ElementList
     */
    public function fill($values);
    
    /**
     * Adds a valid renderable element onto the element list.
     * 
     * @param Europa_Form_Base $element The element to add.
     * @param mixed $offset The offset to set the element at.
     * @return Europa_Form_ElementList
     */
    public function addElement(Europa_Form_Base $element, $offset = null);
}