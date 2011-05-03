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
class Checkbox extends Input
{
    /**
     * Converts the checkbox to a string.
     * 
     * @return string
     */
    public function __toString()
    {
        $this->type = 'checkbox';
        $this->detectChecked();
        return '<input ' . $this->getAttributeString() . ' />';
    }
    
    /**
     * Detects whether or not the element should be checked based on the
     * value of the element.
     * 
     * @return void
     */
    protected function detectChecked()
    {
        if ($this->hasAttribute('checked')) {
            if ($this->getAttribute('checked')) {
                $this->setAttribute('checked', 'checked');
            } else {
                $this->removeAttribute('checked');
            }
        }
    }
}