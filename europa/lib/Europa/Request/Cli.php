<?php

/**
 * A request handler for CLI requests.
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
	 * Constructs a new cli object and parses out the command parameters.
	 * 
	 * @return void
	 */
	public function __construct()
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

			if ($skip) {
				$skip = false;
			}
		}
	}
}