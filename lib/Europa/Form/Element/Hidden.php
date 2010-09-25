<?php

/**
 * A hidden form input.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Hidden extends Europa_Form_Element_Input
{
    /**
     * Constructs and sets defaults.
     * 
     * @return Europa_Form_Element_Hidden
     */
    public function __construct()
    {
        parent::__construct();
        $this->type = 'hidden';
    }
}