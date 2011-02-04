<?php

/**
 * A default form button.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Form\Element
{
    class Button extends Input
    {
        /**
         * Constructs and sets defaults.
         * 
         * @return \Europa\Form\Element\Button
         */
        public function __construct(array $attributes = array())
        {
            // pre-set value that can be overridden
            $this->value = 'Button';
            
            // construct with attributes
            parent::__construct($attributes);
            
            // force submit type
            $this->type = 'button';
        }
    }
}