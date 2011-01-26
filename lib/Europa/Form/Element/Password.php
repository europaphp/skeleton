<?php

/**
 * A default form password input.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Form\Element
{
    class Password extends Input
    {
        /**
         * Constructs and sets defaults.
         * 
         * @return \Europa\Form\Element\Password
         */
        public function __construct(array $attributes = array())
        {
            parent::__construct($attributes);
            $this->type = 'password';
        }
    }
}