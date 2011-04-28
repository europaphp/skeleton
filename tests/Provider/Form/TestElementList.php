<?php

namespace Provider\Form;
use Europa\Form\ElementList;

/**
 * Dummy class for validating the \Europa\Form\ElementList
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class TestElementList extends ElementList
{
    public function __toString()
    {
        return '';
    }
}