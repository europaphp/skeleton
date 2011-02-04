<?php

/**
 * The main form class which is also an element list.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Form
{
    abstract class Base implements Renderable, Validatable
    {
        /**
         * The attributes set on the element.
         * 
         * @var array
         */
        protected $attributes = array();
        
        /**
         * Sets an attribute value.
         * 
         * @param string $name  The name of the attribute.
         * @param string $value The value of the attribute.
         * 
         * @return mixed
         */
        public function __set($name, $value)
        {
            return $this->setAttribute($name, $value);
        }
        
        /**
         * Returns an attribute value.
         * 
         * @param string $name The name of the attribute.
         * 
         * @return mixed
         */
        public function __get($name)
        {
            return $this->getAttribute($name);
        }
        
        /**
         * Returns whether or not an attribute exists.
         * 
         * @param string $name The attribute name.
         * 
         * @return bool
         */
        public function __isset($name)
        {
            return $this->hasAttribute($name);
        }

        /**
         * Removes an attribute.
         * 
         * @param string $name The attribute name.
         * 
         * @return bool
         */
        public function __unset($name)
        {
            return $this->removeAttribute($name);
        }
        
        /**
         * Sets an attribute value.
         * 
         * @param string $name  The name of the attribute.
         * @param string $value The value of the attribute.
         * 
         * @return mixed
         */
        public function setAttribute($name, $value)
        {
            $this->attributes[$name] = $value;
            return $this;
        }
        
        /**
         * Returns an attribute value.
         * 
         * @param string $name The name of the attribute.
         * 
         * @return mixed
         */
        public function getAttribute($name)
        {
            if (isset($this->attributes[$name])) {
                return $this->attributes[$name];
            }
            return null;
        }
        
        /**
         * Returns whether or not an attribute exists.
         * 
         * @param string $name The attribute name.
         * 
         * @return bool
         */
        public function hasAttribute($name)
        {
            return isset($this->attributes[$name]);
        }
        
        /**
         * Removes an attribute.
         * 
         * @param string $name The attribute name.
         * 
         * @return bool
         */
        public function removeAttribute($name)
        {
            if (isset($this->attributes[$name])) {
                unset($this->attributes[$name]);
            }
            return $this;
        }
        
        /**
         * Sets an array of attributes all at once.
         * 
         * @return \Europa\Form\Element
         */
        public function setAttributes(array $attributes = array())
        {
            foreach ($attributes as $name => $value) {
                $this->$name = $value;
            }
            return $this;
        }
        
        /**
         * Returns the attributes.
         * 
         * @return \Europa\Form\Element
         */
        public function getAttributes()
        {
            return $this->attributes;
        }

        /**
         * Formats the properties of the element as an xml attribute string.
         * 
         * @return string
         */
        public function getAttributeString()
        {
            $attrs = array();
            foreach ($this->getAttributes() as $k => $v) {
                $attrs[] = $k . '="' . $v . '"';
            }
            return implode(' ', $attrs);
        }
    }
}