<?php

/**
 * A default form reset button.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Form\Element
{
    class Clear extends Button
    {
        /**
         * Constructs and sets defaults.
         * 
         * @return \Europa\Form\Element\Reset
         */
        public function __construct(array $attributes = array())
        {
            parent::__construct($attributes);
                $this->type  = 'reset';
                $this->value = 'Clear';
            }
        }
    }
}