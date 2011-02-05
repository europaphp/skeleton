<?php

/**
 * The base interface for all renderable form elements.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Form
{
    interface Renderable
    {
        /**
         * Makes sure every renderable object can be rendered.
         * 
         * @return string
         */
        public function __toString();
        
        /**
         * Sets an attribute value.
         * 
         * @param string $name  The name of the attribute.
         * @param string $value The value of the attribute.
         * 
         * @return mixed
         */
        public function __set($name, $value);
        
        /**
         * Returns an attribute value.
         * 
         * @param string $name The name of the attribute.
         * 
         * @return mixed
         */
        public function __get($name);
        
        /**
         * Returns whether or not an attribute exists.
         * 
         * @param string $name The attribute name.
         * 
         * @return bool
         */
        public function __isset($name);

        /**
         * Removes an attribute.
         * 
         * @param string $name The attribute name.
         * 
         * @return bool
         */
        public function __unset($name);
        
        /**
         * Sets an attribute value.
         * 
         * @param string $name  The name of the attribute.
         * @param string $value The value of the attribute.
         * 
         * @return mixed
         */
        public function setAttribute($name, $value);
        
        /**
         * Returns an attribute value.
         * 
         * @param string $name The name of the attribute.
         * 
         * @return mixed
         */
        public function getAttribute($name);
        
        /**
         * Returns whether or not an attribute exists.
         * 
         * @param string $name The attribute name.
         * 
         * @return bool
         */
        public function hasAttribute($name);
        
        /**
         * Removes an attribute.
         * 
         * @param string $name The attribute name.
         * 
         * @return bool
         */
        public function removeAttribute($name);
        
        /**
         * Sets an array of attributes all at once.
         * 
         * @return \Europa\Form\Element
         */
        public function setAttributes(array $attributes = array());
        
        /**
         * Returns the attributes.
         * 
         * @return \Europa\Form\Element
         */
        public function getAttributes();

        /**
         * Formats the properties of the element as an xml attribute string.
         * 
         * @return string
         */
        public function getAttributeString();
    }
}