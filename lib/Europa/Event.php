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
class Europa_Event
{
	/**
	 * The event stack which contains a stack of callback for each event bound.
	 * 
	 * @var array
	 */
	protected static $_stack = array();
	
	/**
	 * Binds an event handler to the stack.
	 * 
	 * @param string $name The name of the event to bind to.
	 * @param mixed $callback The callback to call when triggering.
	 * @return void
	 */
	public static function bind($name, Europa_Event_Triggerable $handler)
	{
		// make sure the event has it's own stack
		if (!self::isBound($name)) {
			self::$_stack[$name] = array();
		}
		
		// and add it to the stack
		self::$_stack[$name][] = $handler;
	}
	
	/**
	 * Unbinds an event.
	 * 
	 * @param string $name The name of the event to unbind.
	 * @param Europa_Event_Triggerable The specific handler to unbind, if specified.
	 * @return bool
	 */
	public static function unbind($name, Europa_Event_Triggerable $handler = null)
	{
		if (self::isBound($name)) {
			if ($handler) {
				foreach (self::$_stack[$name] as $k => $bound) {
					if ($bound === $handler) {
						unset(self::$_stack[$name][$k]);
						return true;
					}
				}
			}
			unset(self::$_stack[$name]);
			return true;
		}
		return false;
	}
	
	/**
	 * Triggers an event stack.
	 * 
	 * @param array $data Any data to pass to the event or event stack at the time
	 * of triggering.
	 * @return bool
	 */
	public static function trigger($name, array $data = array())
	{
		foreach (self::getStack($name) as $handler) {
			if ($handler->trigger($data) === false) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Returns whether the event passed is bound or not.
	 * 
	 * @param string $eventName
	 * @return bool
	 */
	public static function isBound($name)
	{
		return isset(self::$_stack[$name]);
	}
	
	/**
	 * Returns the event stack.
	 * 
	 * @return array
	 */
	public static function getStack($name = null)
	{
		if ($name) {
			if (self::isBound($name)) {
				return self::$_stack[$name];
			}
			return array();
		}
		return self::$_stack;
	}
}