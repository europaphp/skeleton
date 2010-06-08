<?php

/**
 * The request class for representing a CLI request.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Request_Cli extends Europa_Request
{
	/**
	 * Constructs a CLI request and sets defaults. By default, no layout or
	 * view is rendered.
	 * 
	 * @return Europa_Request_Cli
	 */
	public function __construct()
	{
		$this->_parseParams()
		     ->setRouteSubject(implode(' ', $_SERVER['argv']));
	}
	
	/**
	 * Parses out the cli request parameters - in unix style - and sets them on
	 * the request.
	 * 
	 * @return Europa_Request_Cli
	 */
	protected function _parseParams()
	{
		$args = $_SERVER['argv'];
		$skip = false;
		array_shift($args);
		foreach ($args as $index => $param) {
			// parse out named params
			if (strpos($param, '--') === 0) {
				$param = substr($param, 2, strlen($param));
				if (isset($args[$index + 1])) {
					$this->setParam($param, $args[$index + 1]);
					
					$skip = true;
				} else {
					$this->setParam($param, true);
				}
				continue;
			}
			// parse out flags
			if (strpos($param, '-') === 0) {
				$param    = substr($param, 1, strlen($param));
				$flags    = str_split($param);
				$lastFlag = '';
				foreach ($flags as $flag) {
					// set flags
					$this->setParam($flag, true);
					// set last flag
					$lastFlag = $flag;
				}
				// if there is a value for the flag, set the last one
				if (isset($args[$index + 1])) {
					$this->setParam($lastFlag, $args[$index + 1]);
					$skip = true;
				} else {
					$this->setParam($param, true);
				}
				continue;
			}
			// if skipping was set, reset it
			if ($skip) {
				$skip = false;
			}
		}
		
		return $this;
	}
}