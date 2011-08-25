<?php

namespace Europa\Form\Element;

/**
 * A default form input element.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Input extends ElementAbstract
{
    /**
     * Constructs and sets defaults.
     * 
     * @return \Europa\Form\Element\Input
     */
    public function __construct(array $attributes = array())
    {    
        $this->type = 'text';
        parent::__construct($attributes);
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
