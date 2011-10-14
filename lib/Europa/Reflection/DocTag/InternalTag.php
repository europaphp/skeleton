<?php

namespace Europa\Reflection\DocTag;
use Europa\Reflection\DocTag;

/**
* Represents a docblock internal tag.
*
* @category Reflection
* @package  Europa
* @author   Trey Shugart <treshugart@gmail.com>
* @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
*/
class InternalTag extends DocTag
{
    /**
     * Returns the name of the tag.
     * 
     * @return string
     */
    public function tag()
    {
        return 'internal';
    }
    
    /**
     * Returns the description.
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->tagString;
    }
}
