<?php

namespace Europa\Reflection\DocTag;
use \Europa\Reflection\DocTag;

class DeprecatedTag extends DocTag
{
    /**
     * Returns the name of the tag.
     * 
     * @return string
     */
    public function tag()
    {
        return 'deprecated';
    }
    
    /**
     * Returns the reason for deprecation.
     * 
     * @return string
     */
    public function getReason()
    {
        return $this->tagString;
    }
}