<?php

namespace Europa\Reflection;

/**
 * Makes sure that that classes are able to return a docblock reflection object.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) Trey Shugart http://europaphp.org/license
 */
interface ReflectorInterface
{
    /**
     * Returns the appropriate doc block instance.
     * 
     * @return \Europa\Reflection\DocBlock
     */
    public function getDocBlock();
}
