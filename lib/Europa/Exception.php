<?php

/**
 * @file
 * 
 * @package    Europa
 * @subpackage Exception
 */

/**
 * @class
 * 
 * @name Europa_Exception
 * @desc Extends the default exception handler, using most of it's functionality,
 *       but allowing an event hook as well as a exception message. Eruopa_Exception
 *       Exceptions in Europa can be thrown without having to trap it in a try/catch 
 *       block, providing a common way to handle all types of errors.
 */
class Europa_Exception extends Exception
{
	const
		/**
		 * @constant
		 * @event
		 * 
		 * @name EVENT_BEFORE_OUTPUT
		 * @desc The event that gets fired before the exception message is displayed.
		 */
		EVENT_BEFORE_OUTPUT = 'Europa_Exception.beforeOutput';
	
	
	
	/**
	 * @method
	 * @magic
	 * 
	 * @name __construct
	 * @desc Constructs the Exception class and supplies a default exception message/output. It triggers an event before the
	 *       exception is output so we can hook into this for whatever the case may be.
	 * 
	 * @param String[Optional]  $message - The exception message. If not set, this defaults to 'An unknown exception was thrown'.
	 * @param Integer[Optional] $code    - The exception code. If not set, this defaults to 0.
	 * 
	 * @return Void
	 */
	public function __construct($message = 'An unknown exception was thrown.', $code = 0)
	{
		// construct the Exception class
		parent::__construct($message, $code);
		
		// Triggers bound events for Europa_Exception.beforeOutput. Could be a custom dispatch 
		// call, view object, etc. In order to prevent Europa_Exception::__toString() from 
		// executing the user must call either die or exit within one of the event handlers.
		Europa_Event::trigger(self::EVENT_BEFORE_OUTPUT, array('exception' => $this));
	}
	
	/**
	 * @method
	 * @magic
	 * 
	 * @name __toString
	 * @desc Outputs the error message along with the exception details and call-stack. A good default
	 *       error message is provided without configuration/extending, etc. The call-stack is displayed
	 *       in chronological order - the first being the first call, etc.
	 *  
	 * @return Void
	 */
	public function __toString()
	{
		$str  = '
			<style type="text/css">
				body {
					font-family : Arial, Tahoma, Verdana, Helvetica, Sans-Serif;
					font-size   : 0.8em;
					color       : #444;
				}
				ul {
					
				}
				ul li {
					list-style-type : none;
				}
				.file {
					color : #333;
				}
				.class {
					color : green;
				}
				.method {
					color : red;
				}
				.line {
					color : red;
				}
				.arg {
					color      : blue;
					font-style : italic;
				}
				.args {
					font-family : courier new;
					color       : #666;
					padding     : 0px;
				}
				.args ul {
					 padding : 5px 20px;
				}
				.light {
					color : #ccc;
				}
				.small {
					font-size : 0.8em;
				}
			</style>
			
			<h1>Europa Exception</h1>
			
			<p>' . $this->getMessage() . '</p>
			
			<h2>Details</h2>
			
			<table style="border-collapse: collapse; border: 1px solid #ccc;">
		';
		
		foreach ($this as $k => $v) {
			if ($v === $this->getMessage()) {
				continue;
			}
			
			$str .= '
				<tr>
					<td style="font-weight: bold; padding: 2px 4px; border: 1px solid #ddd; vertical-align: top; text-align: right;">' . $k . '</td>
					<td style=" padding: 2px 4px; border: 1px solid #ddd;">' . $v . '</td>
				</tr>
			';
		}
		
		$str .= '
			</table>
			
			<h2>Call Stack</h2>
			<ol>
		';
		
		foreach (array_reverse($this->getTrace()) as $trace) {
			if (!isset($trace['file'])) {
				continue;
			}
			
			$args  = array();
			$class = isset($trace['class'])
				? '<span class="class">' . $trace['class'] . $trace['type'] . '</span>'
				: '';
			
			foreach ($trace['args'] as $argKey => $argVal) {
				$args[] = '<span class="arg">' . ucfirst(gettype($argVal)) . '</span>';
			}
			
			$str .= '<li>'
				 .  '<h3 class="file">' . $trace['file'] . ' : <span class="line">' . $trace['line'] . '</span></h3>'
				 .  '<p>' . $class . '<span class="method">' . $trace['function'] . '(' . implode(', ', $args) . ')</span></p>'
				 .  $this->_getArgList($trace['args'])
				 .  '</li>';
		}
		
		$str .= '
			</ol>
		';
		
		return $str;
	}
	
	/**
	 * @name handle
	 * @desc The default exception handler that is defined after Europa_Controller is included. This allows easy
	 *       extending of Europa_Exception so you can implement custom exceptions and handle them in different
	 *       ways before they are output while automating the calling of __toString.
	 * 
	 * @param Object $e - An exception of any kind. As long as the __toString method is implemented, then this will work.
	 * 
	 * @return Void
	 */
	static public function handle($e)
	{
		die($e);
	}
	
	/**
	 * @name trigger
	 * @desc Triggers an exception. Provides a way to trigger an exception-like error where exceptions can't be thrown.
	 *       Same as using Europa_Exception::handle(new Europa_Exception($message, $code)).
	 * 
	 * @param String  $message - The message to send to the exception.
	 * @param Integer $code    - The exception code.
	 */
	static public function trigger($message = 'An unknown error has occured.', $code = 0)
	{
		self::handle(new self($message, $code));
	}
	
	
	
	/**
	 * @method
	 * @private
	 * 
	 * @name _getArgList
	 * @desc Returns a formatted string of arugments from the passed array of arguments.
	 * 
	 * @param Array $arr - The array of arguments to format.
	 * 
	 * @return String 
	 */
	private function _getArgList($arr)
	{
		if (!is_array($arr) || count($arr) === 0) {
			return null;
		}
		
		$str = '<ul class="args">';
		
		foreach ($arr as $v) {
			$value = $v;
			$len   = null;
			
			if (is_scalar($value)) {
				$value = (string) $value;
				$len   = is_string($v)
					? '(' . strlen($value) . ')'
					: '';
			} else {
				$value = $this->_getArgList($v);
				$len   = '(' . count($v) . ')';
			}
			
			$str .= '<li>'
			     .  '<em class="small">' . ucfirst(gettype($v)) . $len . '</em> ' . $value
			     .  '<li>';
		}
		
		$str .= '</ul>';
		
		return $str;
	}
}