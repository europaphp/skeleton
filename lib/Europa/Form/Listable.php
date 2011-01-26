<?php

/**
 * Allows nested lists of elements.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Form
{
    interface Listable extends \ArrayAccess, \Iterator
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
         * 
         * @return \Europa\Form\Listable
         */
        public function fill($values);
        
        /**
         * Adds a valid renderable element onto the element list.
         * 
         * @param \Europa\Form\Base $element The element to add.
         * @param mixed             $offset  The offset to set the element at.
         * 
         * @return \Europa\Form\Listable
         */
        public function addElement(\Europa\Form\Base $element, $offset = null);
    }
}