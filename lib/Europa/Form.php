<?php

namespace Europa;
use Europa\Form\ElementList;

/**
 * The main form class which is also an element list.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Form extends ElementList
{
    /**
     * The default method for sending the form.
     * 
     * @var string
     */
    const DEFAULT_METHOD = 'post';
    
    /**
     * Converts the form to a string.
     * 
     * @return string
     */
    public function __toString()
    {
        if (!$this->hasAttribute('method')) {
            $this->setAttribute('method', self::DEFAULT_METHOD);
        }
        
        $str  = '<form' . $this->getAttributeString() . '>';
        $str .= parent::toString();
        $str .= '</form>';
        return $str;
    }
}
