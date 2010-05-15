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
abstract class Europa_Request_Route
{
	/**
	 * A way of retrieving a uri representing the route.
	 * 
	 * @return string
	 */
	abstract public function getUri(array $params = array());
	
	/**
	 * An algorithm for matching the passed $subject to the expression
	 * set on the route. Returns an array of matched parameters or
	 * false on failure.
	 * 
	 * @param string $subject The string to match against the route.
	 * @return array|bool
	 */
	abstract public function match($subject);
}