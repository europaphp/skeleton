<?php

namespace Europa\Form\Element;

/**
 * A hidden form input.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Hidden extends Input
{
    /**
     * Constructs and sets defaults.
     * 
     * @return \Europa\Form\Element\Hidden
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->type = 'hidden';
    }
}