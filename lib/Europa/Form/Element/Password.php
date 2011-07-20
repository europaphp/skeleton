<?php

namespace Europa\Form\Element;

/**
 * A default form password input.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
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