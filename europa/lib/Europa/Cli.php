<?php

/**
 * A mini framework for handling requests on the command line.
 * 
 * @category Cli
 * @package  Europa
 * @author   Trey Shugart
 * @license  (c) 2010 Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license
 */
class Europa_Cli
{
	/**
	 * Holds the name and value of all of the commands that were parsed out.
	 * 
	 * @var array
	 */
	private static $_commands = array();
	
	/**
	 * Holds the name and value of all of the parameters that were parsed out.
	 * 
	 * @var array
	 */
	private static $_params = array();
	
	/**
	 * Constructs a new cli object and parses out the command parameters.
	 * 
	 * @return void
	 */
	public static function init()
	{
		$args = $_SERVER['argv'];
		$skip = false;

		array_shift($args);

		foreach ($args as $index => $param) {
			// parse out named params
			if (strpos($param, '--') === 0) {
				$param = substr($param, 2, strlen($param));

				if (isset($args[$index + 1])) {
					self::$_params[$param] = $args[$index + 1];
					
					$skip = true;
				} else {
					self::$_params[$param] = true;
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
					self::$_params[$flag] = true;
					
					// set last flag
					$lastFlag = $flag;
				}

				// if there is a value for the flag, set the last one
				if (isset($args[$index + 1])) {
					self::$_params[$lastFlag] = $args[$index + 1];
					
					$skip = true;
				} else {
					self::$_params[$param] = true;
				}

				continue;
			}

			if ($skip) {
				$skip = false;
			} else {
				self::$_commands[] = $param;
			}
		}
	}
	
	/**
	 * Returns a command that was typed in.
	 * 
	 * @param mixed $index   The index of the command to get.
	 * @param mixed $default The default value to return if no command is
	 *                       found.
	 * 
	 * @return mixed
	 */
	public static function getCommand($index, $default = null)
	{
		if (isset(self::$_commands[$index])) {
			return self::$_commands[$index];
		}
		
		return $default;
	}

	/**
	 * Returns all commands that were passed in.
	 * 
	 * @return array
	 */
	public static function getCommands()
	{
		return self::$_commands;
	}

	/**
	 * Returns a single parameter specified by $name. If $name is an array,
	 * it searches for all parameters matched and returns the first match. This
	 * is useful when you have a flag (-f) or a named parameter (--flag) that
	 * reference the same value, for example array('f', 'flag').
	 * 
	 * @param mixed $names   The name(s) of the parameters to retrieve. The 
	 *                       first matched parameter will be returned.
	 * @param mixed $default The default value to return if no parameter is
	 *                       found.
	 * @return mixed
	 */
	public static function getParam($names, $default = null)
	{
		if (!is_array($names)) {
			$names = array($names);
		}
		
		foreach ($names as $name) {
			if (isset(self::$_params[$name])) {
				return self::$_params[$name];
			}
		}
		
		return $default;
	}

	/**
	 * Returns all parameters that were passed as an array. The key will be the
	 * name or flag of the parameter and the value the value. Flags or params
	 * that do not have values will contain (bool) true.
	 * 
	 * @return array
	 */
	public static function getParams()
	{	
		return self::$_params;
	}
}