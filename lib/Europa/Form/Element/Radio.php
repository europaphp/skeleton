<?php

namespace Europa\Form\Element;

/**
 * A generic radio button.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Radio extends Checkbox
{
    /**
     * Returns the radio button as a string.
     * 
     * @return string
     */
    public function __toString()
    {
        $this->type = 'radio';
        $this->detectChecked();
        return '<input ' . $this->getAttributeString() . ' />';
    }
}