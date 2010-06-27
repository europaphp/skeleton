<?php

/**
 * Provides a base implementation for routes.
 * 
 * @category Routing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
abstract class Europa_Router_Route
{
	/**
	 * Provides a way to reverse engineer the route using named parameters.
	 * 
	 * @return string
	 */
	abstract public function reverse(array $params = array());
	
	/**
	 * An algorithm for matching the passed $subject to the expression
	 * set on the route. Returns an array of matched parameters or
	 * false on failure.
	 * 
	 * @param string $subject The string to query against the route.
	 * @return array|bool
	 */
	abstract public function query($subject);
}