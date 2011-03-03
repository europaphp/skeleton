<?php

namespace Europa\Form\Element;

/**
 * A default form button.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Button extends \Europa\Form\Element
{
    /**
     * Converts the button to a string.
     * 
     * @return string
     */
    public function __toString()
    {
        $this->type = 'button';
        if (!$this->label) {
            $this->label = 'Button';
        }
        return '<button ' . $this->getAttributeString() . '>' . $this->label . '</button>';
    }
}