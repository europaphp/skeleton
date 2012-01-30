<?php

namespace Europa\Reflection\DocTag;
use Europa\Reflection\DocTag;

/**
* Represents a docblock license tag.
*
* @category Reflection
* @package  Europa
* @author   Trey Shugart <treshugart@gmail.com>
* @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
*/
class LicenseTag extends DocTag
{
    /**
    * Returns the type of the tag.
    *
    * @return string
    */
    public function tag()
    {
        return 'license';
    }
}
