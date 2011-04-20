<?php

namespace Europa\Loader;
use Europa\Exception as BaseException;

/**
 * Loader exception class.
 * 
 * @category Exceptions
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Exception extends BaseException
{
    /** 
     * Thrown when no load paths are defined and a load is attempted.
     * 
     * @var int
     */
    const NO_PATHS_DEFINED = 1;
    
    /**
     * Thrown when added path cannot be resolved.
     * 
     * @var int
     */
    const INVALID_PATH = 2;
}