<?php

namespace Europa\Reflection\DocTag;
use \Europa\Reflection\DocTag;

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