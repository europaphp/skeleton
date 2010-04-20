<?php

/**
 * @author Trey Shugart
 */

/**
 * Provides a fluid object oriented way to manipulate strings.
 *
 * @package Europa
 * @subpackage String
 */
class Europa_String
{
	/**
	 * Holds a reference to the current string.
	 * 
	 * @var string
	 */
	private $_string;

	/**
	 * Constructs a new string object from the passed in string.
	 *
	 * @param string $string The string to manipulate.
	 * @return Europa_String
	 */
	public function __construct($string = '')
	{
		if ($string === true) {
			$string = 'true';
		} elseif ($string === false) {
			$string = 'false';
		} elseif (is_numeric($string)) {
			$string = (string) $string;
		} elseif (is_array($string)) {
			$string = serialize($string);
		} elseif (is_null($string)) {
			$string = 'null';
		}
		
		$this->_string = (string) $string;
	}

	/**
	 * Converts the string object back to a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->_string;
	}

	/**
	 * Camelcases a string to Europa Conventions.
	 *
	 * @param boolean $ucFirst Whether or not to capitalize the first letter.
	 * @return string
	 */
	public function camelCase($ucFirst = false)
	{
		$str = $this->_string;
		$str = urldecode($str);
		$str = str_replace(DIRECTORY_SEPARATOR, '/', $str);
		$str = trim($str, '/');
		$str = str_replace('_', '/', $str);

		// if a forward slash is passed, auto ucfirst
		$autoUcFirst = strpos($str, '/') !== false;
		$parts		 = explode('/', $str);

		foreach ($parts as $k => $v) {
			$subParts = preg_split('/[^a-zA-Z0-9]/', $v);

			foreach ($subParts as $kk => $vv) {
				$subParts[$kk] = ucfirst($vv);
			}

			$parts[$k] = implode('', $subParts);
		}

		$str = implode('_', $parts);

		if ($autoUcFirst || $ucFirst) {
			$str = ucfirst($str);
		} elseif (isset($str{0})) {
			$str{0} = strtolower($str{0});
		}

		$this->_string = $str;

		return $this;
	}

	/**
	 * Same as PHP trim() function, but put in to allow for chaining.
	 *
	 * @param string $charList Same as the char-list in PHP's trim() function.
	 * @return string.
	 */
	public function trim($charList = null)
	{
		$this->_string = trim($this->_string, $charList);

		return $this;
	}

	/**
	 * Same as PHP ltrim() function, but put in to allow for chaining.
	 *
	 * @param string $charList Same as the charlist in PHP's ltrim() function.
	 * @return string.
	 */
	public function ltrim($charList = null)
	{
		$this->_string = ltrim($this->_string, $charList);

		return $this;
	}

	/**
	 * Same as PHP rtrim() function, but put in to allow for chaining.
	 *
	 * @param string $charList Same as the charlist in PHP's rtrim() function.
	 * @return string.
	 */
	public function rtrim($charList = null)
	{
		$this->_string = rtrim($this->_string, $charList);

		return $this;
	}
	
	/**
	 * Takes a value and type casts it. Strings such as 'true' or 'false' 
	 * will be converted to a boolean value. Numeric strings will be converted
	 * to integers or floats and empty strings are converted to NULL values.
	 *		 
	 * @param mixed $val The value to cast and return.
	 * @return mixed
	 */
	public function cast()
	{
		$val = urldecode($this->_string);

		if (strtolower($val) == 'true') {
			return true;
		}

		if (strtolower($val) == 'false') {
			return false;
		}
		
		if (!$val || strtolower($val) == 'null') {
			return null;
		}

		if (is_string($val) && is_numeric($val)) {
			if (strpos($val, '.') === false) {
				$val = (int) $val;
			} else {
				$val = (float) $val;
			}
		}

		return $val;
	}

	/**
	 * Creates a new string. Same as calling new Europa_String($string).
	 *
	 * @param string $string The string the object should represent.
	 * @return string
	 */
	public static function create($string = '')
	{
		return new self($string);
	}
}