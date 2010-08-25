<?php

/**
 * An event class for managing multiple events and event stacks.
 * 
 * @category  Events
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
class Europa_Event_Callback implements Europa_Event_Triggerable
{
	/**
	 * The callable callback that will be triggered.
	 * 
	 * @var mixed
	 */
	protected $_callback;
	
	/**
	 * Flags whether or not the current callback has been triggered.
	 * 
	 * @var bool
	 */
	protected $_triggered = false;
	
	/**
	 * Constructs a new callback event.
	 * 
	 * @param mixed $callback The callable callback to trigger.
	 * @return Europa_Event_Callback
	 */
	public function __construct($callback)
	{
		if (!is_callable($callback, true)) {
			throw new Europa_Event_Exception(
				'Passed callback is not callable.',
				Europa_Event_Exception::INVALID_CALLBACK
			);
		}
		$this->_callback = $callback;
	}
	
	/**
	 * Calls the callback passing the current object data into it.
	 * 
	 * @param array $data The data passed to the event at the time of triggering.
	 * @return mixed
	 */
	public function trigger(array $data = array())
	{
		// and return the return value of the callback passing in the event handler
		return call_user_func($this->_callback, $data);
	}
}