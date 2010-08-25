<?php

/**
 * A basic interface for defining whether or not something is triggerable.
 * 
 * @category  Events
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
interface Europa_Event_Triggerable
{
	/**
	 * Ensures the event manager knows what to do.
	 * 
	 * @return bool
	 */
	public function trigger(array $data = array());
}