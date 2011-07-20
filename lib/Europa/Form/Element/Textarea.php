<?php

namespace Europa\Form\Element;

/**
 * A default form textarea input.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Textarea extends ElementAbstract
{
    /**
     * Renders the textarea element.
     * 
     * @return string
     */
    public function __toString()
    {
        $attr = $this->getAttributeString();
        return '<textarea'
             . ($attr ? ' ' . $attr : '')
             . '>'
             . $this->value
             . '</textarea>';
    }
}