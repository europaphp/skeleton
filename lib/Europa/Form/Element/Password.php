<?php

/**
 * A default form password input.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Password extends Europa_Form_Element_Input
{
    /**
     * Renders the password input element.
     * 
     * @return string
     */
    public function __toString()
    {
        $this->type = 'password';
        
        return parent::__toString();
    }
}