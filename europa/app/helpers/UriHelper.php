<?php

/**
 * A helper for formatting a passed in URI.
 * 
 * @category Helpers
 * @package  UriHelper
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class UriHelper
{
	/**
	 * Formats a URI using the active request instance.
	 * 
	 * @param string $uri The URI to format.
	 * @param array $params The params, if any, to pass to the route.
	 * @return UriHelper
	 */
	public function uri($uri = null, array $params = array())
	{
		return Europa_Request_Http::getActiveInstance()
		     ->formatUri($uri, $params);
	}
}