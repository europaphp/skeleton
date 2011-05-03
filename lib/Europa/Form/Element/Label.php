<?php

namespace Europa\Form\Element;

/**
 * A default label element.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Label extends \Europa\Form\Element
{
    /**
     * Converts the label to a string.
     * 
     * @return string
     */
    public function __toString()
    {
        $label = $this->getAttribute('label');
        $this->removeAttribute('label');
        if ($this->hasAttribute('id')) {
            $this->setAttribute('for', $this->getAttribute('id'));
            $this->removeAttribute('id');
        }
        return '<label ' . $this->getAttributeString() . '>' . $label . '</label>';
    }
}