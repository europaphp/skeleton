<?php

namespace Europa\Reflection\DocTag;
use \Europa\Reflection\DocTag;

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