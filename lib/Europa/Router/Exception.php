<?php

namespace Europa\Router;
use Europa\Exception as BaseException;

/**
 * The exception class for \Europa\Router.
 * 
 * @category Exceptions
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Exception extends BaseException
{
    /**
     * Thrown when no route is matched.
     * 
     * @var int
     */
    const NO_MATCH = 1;
}