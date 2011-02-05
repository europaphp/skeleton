<?php

namespace Europa\Form;

/**
 * Interface for form validation.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface Validatable
{
    /**
     * Validates the value(s) against the set validator.
     * 
     * @return \Europa\Form\Validatable
     */
    public function validate();
    
    /**
     * Returns whether or not the last validation was successful.
     * 
     * @return bool
     */
    public function isValid();
    
    /**
     * Returns the messages if validation failed or an empty array.
     * 
     * @return array
     */
    public function getMessages();
}