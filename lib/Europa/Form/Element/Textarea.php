<?php

/**
 * A default form textarea input.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Form\Element
{
    class Textarea extends \Europa\Form\Element
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
}