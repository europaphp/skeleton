<?php

/**
 * A default form submit button.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Form\Element
{
    class Submit extends Button
    {
        /**
         * Constructs and sets defaults.
         * 
         * @return \Europa\Form\Element\Submit
         */
        public function __construct(array $attributes = array())
        {
            // pre-set value that can be overridden
            $this->value = 'Submit';
            
            // construct with attributes
            parent::__construct($attributes);
            
            // force submit type
            $this->type = 'submit';
        }
    }
}