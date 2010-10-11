<?php

/**
 * A default form reset button.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Reset extends Europa_Form_Element_Button
{
    /**
     * Constructs and sets defaults.
     * 
     * @return Europa_Form_Element_Reset
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
            $this->type  = 'reset';
            $this->value = 'Reset';
        }
    }