<?php

/**
 * Loader exception class.
 * 
 * @category Exceptions
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Loader
{
    class Exception extends \Europa\Exception
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
}