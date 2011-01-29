<?php

/**
 * A default form input.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Form\Element
{
    class Input extends \Europa\Form\Element
    {
        /**
         * Constructs and sets defaults.
         * 
         * @return \Europa\Form\Element\Input
         */
        public function __construct(array $attributes = array())
        {
            parent::__construct($attributes);
            $this->type = 'text';
        }
        
        /**
         * Renders the reset element.
         * 
         * @return string
         */
        public function __toString()
        {
            // by default, it's a text field
            $attr = $this->getAttributeString();
            return '<input'
                 . ($attr ? ' ' . $attr : '')
                 . ' />';
        }
    }
}