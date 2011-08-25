<?php

namespace Europa\Form;

/**
 * Defines a form element that is listable.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface Listable extends \ArrayAccess, \Iterator
{
    /**
     * Returns the form elements applied to this list.
     * 
     * @return array
     */
    public function getElements();
    
    /**
     * Takes an array of values and searches for a matching value for each element in the list. Recursively handles
     * nested element lists.
     * 
     * @param mixed $values An object or array of values fill the element list with.
     * 
     * @return \Europa\Form\Listable
     */
    public function fill($values);
    
    /**
     * Adds an element onto the element list.
     * 
     * @param \Europa\Form\FormAbstract $element The element to add.
     * @param mixed                     $offset  The offset to set the element at.
     * 
     * @return \Europa\Form\Listable
     */
    public function addElement(FormAbstract $element, $offset = null);
}
