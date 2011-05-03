<?php

namespace Europa\Unit;

/**
 * Base output class for outputting a suite result.
 * 
 * @category UnitTesting
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Output
{
	/**
     * Returns the line break element depending on if the test was
     * accessed via CLI or web.
     * 
     * @return string
     */
    public static function breaker($times = 1)
    {
        $breaker = '<br />';
        if (self::isCli()) {
            $breaker = "\n";
        }
        return str_repeat($breaker, $times);
    }

	/**
     * Returns the word spacer depending on if the test was
     * accessed via CLI or web.
     * 
     * @return string
     */
    public static function spacer($times = 1)
    {
        $spacer = '&nbsp;';
        if (self::isCli()) {
            $spacer = ' ';
        }
        return str_repeat($spacer, $times);
    }

    /**
     * Returns whether or not PHP is being run in CLI mode.
     * 
     * @return bool
     */
    public static function isCli()
    {
        return defined('STDIN');
    }
}