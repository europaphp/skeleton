<?php

namespace Europa\Reflection\DocTag;
use Europa\Reflection\DocTag;

/**
* Represents a docblock see tag.
*
* @category Reflection
* @package  Europa
* @author   Trey Shugart <treshugart@gmail.com>
* @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
*/
class SeeTag extends DocTag
{
    /**
     * Returns the name of the tag.
     * 
     * @return string
     */
    public function tag()
    {
        return 'see';
    }
    
    /**
     * Returns the name of the reference.
     * 
     * @return string
     */
    public function getReference()
    {
        return $this->tagString;
    }
}