<?php

/**
 * A default form button.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Button extends Europa_Form_Element_Input
{
    /**
     * Constructs and sets defaults.
     * 
     * @return Europa_Form_Element_Button
     */
    public function __construct()
    {
        parent::__construct();
        $this->type  = 'button';
        $this->value = 'Button';
    }
}