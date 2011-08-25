<?php

namespace Europa\Form\Element;

/**
 * A default form submit button.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Submit extends Button
{
    /**
     * Converts the button to a string.
     * 
     * @return string
     */
    public function __toString()
    {
        $this->type = 'submit';
        if (!$this->label) {
            $this->label = 'Submit';
        }
        
        $label = $this->label;
        unset($this->label);
        return '<button ' . $this->getAttributeString() . '>' . $label . '</button>';
    }
}
