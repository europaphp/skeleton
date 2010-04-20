<?php

/**
 * A view class for rendering JSON data from bound parameters.
 * 
 * @category View
 * @package  Europa
 * @author   Trey Shugart
 * @license  (c) 2010 Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license
 */
class Europa_View_Json extends Europa_View
{
	/**
	 * Constructs the view and sets parameters.
	 * 
	 * @param array $params
	 */
	public function __construct($params = null)
	{
		$this->_params = $params;
	}
	
	/**
	 * JSON encodes the parameters on the view and returns them.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return json_encode($this->_params);
	}
}