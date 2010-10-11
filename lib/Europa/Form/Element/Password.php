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
     * Constructs and sets defaults.
     * 
     * @return Europa_Form_Element_Password
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->type = 'password';
    }
}