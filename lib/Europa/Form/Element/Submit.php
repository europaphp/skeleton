<?php

/**
 * A default form submit button.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Submit extends Europa_Form_Element_Button
{
    /**
     * Constructs and sets defaults.
     * 
     * @return Europa_Form_Element_Submit
     */
    public function __construct(array $attributes = array())
    {
        // pre-set value that can be overridden
        $this->value = 'Submit';
        
        // construct with attributes
        parent::__construct($attributes);
        
        // force submit type
        $this->type = 'submit';
    }
}