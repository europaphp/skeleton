<?php

/**
 * A default form input.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Input extends Europa_Form_Element
{
    /**
     * Constructs and sets defaults.
     * 
     * @return Europa_Form_Element_Input
     */
    public function __construct()
    {
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